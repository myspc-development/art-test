<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/update/diagnostics', [
        'methods'  => 'GET',
        'callback' => function () {
            $repo = get_option('ap_github_repo_url');
            if (!$repo) {
                return ['error' => 'No repo URL configured'];
            }

            $api = str_replace('https://github.com/', 'https://api.github.com/repos/', rtrim($repo, '/')) . '/releases/latest';
            $resp = wp_remote_get($api, ['timeout' => 10]);

            return [
                'repo'      => $repo,
                'api'       => $api,
                'http_code' => wp_remote_retrieve_response_code($resp),
                'body'      => json_decode(wp_remote_retrieve_body($resp), true),
                'checked_at'=> current_time('mysql')
            ];
        },
        'permission_callback' => function () {
            if (!current_user_can('update_plugins')) {
                return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
            }
            return true;
        }
    ]);
});
