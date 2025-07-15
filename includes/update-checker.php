<?php
if (!defined('ABSPATH')) {
    exit;
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

add_action('plugins_loaded', function () {
    $repo = get_option('ap_github_repo_url');
    if (empty($repo)) {
        return;
    }
    $repo          = rtrim(trim($repo), '/') . '/';

    $updateChecker = PucFactory::buildUpdateChecker(
        $repo,
        ARTPULSE_PLUGIN_FILE,
        'artpulse-management'
    );
    $updateChecker->setBranch('main');

    $updateChecker->addFilter('request_info_result', function ($info, $result) {
        if ($info === null) {
            if (is_wp_error($result)) {
                error_log('Update error: ' . $result->get_error_message());
            } else {
                $body = is_array($result) ? wp_remote_retrieve_body($result) : '';
                error_log('Update error: invalid response ' . $body);
            }
        }
        return $info;
    }, 10, 2);
});

function ap_fetch_latest_release(bool $force = false)
{
    $cached = get_transient('ap_latest_github_release');
    if ($cached && !$force) {
        return $cached;
    }

    $repo = get_option('ap_github_repo_url');
    if (!$repo) {
        return false;
    }
    $repo = trim($repo);
    $repo = preg_replace('#^https?://github.com/#', '', $repo);
    $repo = rtrim($repo, '/');
    $token = get_option('ap_github_token');

    $api  = "https://api.github.com/repos/{$repo}/releases/latest";
    $args = [
        'headers' => [
            'User-Agent' => 'ArtPulse-Updater',
            'Accept'     => 'application/vnd.github+json',
        ],
    ];
    if ($token) {
        $args['headers']['Authorization'] = 'token ' . $token;
    }

    $res = wp_remote_get($api, $args);
    if (is_wp_error($res)) {
        return false;
    }
    $data = json_decode(wp_remote_retrieve_body($res), true);
    if (empty($data['tag_name'])) {
        return false;
    }

    $release = [
        'version' => ltrim($data['tag_name'], 'v'),
        'url'     => $data['html_url'] ?? '',
        'notes'   => $data['body'] ?? '',
    ];
    set_transient('ap_latest_github_release', $release, HOUR_IN_SECONDS * 6);
    return $release;
}

function ap_check_for_new_release(): void
{
    $release = ap_fetch_latest_release(true);
    if (!$release) {
        return;
    }
    update_option('ap_latest_release_info', $release);
    if (defined('ARTPULSE_VERSION') && version_compare($release['version'], ARTPULSE_VERSION, '>')) {
        update_option('ap_update_available', 1);
    }
}

