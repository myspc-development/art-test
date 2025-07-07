<?php
namespace ArtPulse\Admin;

class UpdatesTab
{
    public static function register(): void
    {
        add_action('admin_post_ap_check_updates', [self::class, 'check_updates']);
        add_action('admin_post_ap_run_update', [self::class, 'run_update']);
        add_action('init', [self::class, 'schedule_cron']);
        add_action('ap_daily_update_check', [self::class, 'maybe_auto_update']);
    }

    public static function schedule_cron(): void
    {
        if (!wp_next_scheduled('ap_daily_update_check')) {
            wp_schedule_event(time(), 'daily', 'ap_daily_update_check');
        }
    }

    public static function maybe_auto_update(): void
    {
        $opts = get_option('artpulse_settings', []);
        if (empty($opts['auto_update_enabled'])) {
            return;
        }
        if (self::check_updates(true)) {
            self::run_update(true);
        }
    }

    private static function parse_repo(string $url): array
    {
        $url = rtrim($url, '/');
        $url = preg_replace('/\.git$/', '', $url);
        $parts = parse_url($url);
        $path  = trim($parts['path'] ?? '', '/');
        if (strpos($path, '/') === false) {
            return ['', ''];
        }
        [$owner, $repo] = explode('/', $path, 2);
        return [$owner, $repo];
    }

    private static function get_repo_info(): array
    {
        $opts = get_option('artpulse_settings', []);
        return [
            'url'    => $opts['update_repo_url'] ?? '',
            'branch' => $opts['update_branch'] ?? 'main',
            'token'  => $opts['update_access_token'] ?? '',
        ];
    }

    public static function check_updates(bool $silent = false): bool
    {
        if (!current_user_can('manage_options')) {
            if ($silent) {
                return false;
            }
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        $info = self::get_repo_info();
        if (empty($info['url'])) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return false;
        }
        [$owner, $repo] = self::parse_repo($info['url']);
        if (empty($owner) || empty($repo)) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return false;
        }
        $api = "https://api.github.com/repos/{$owner}/{$repo}/commits/{$info['branch']}";
        $args = [
            'headers' => [
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'ArtPulse-Updater',
            ],
        ];
        if (!empty($info['token'])) {
            $args['headers']['Authorization'] = 'token ' . $info['token'];
        }
        $response = wp_remote_get($api, $args);
        if (is_wp_error($response)) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return false;
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['sha'])) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return false;
        }
        $remote_sha = $body['sha'];
        $current_sha = get_option('ap_current_repo_sha');
        update_option('ap_update_last_check', current_time('mysql'));
        if ($remote_sha !== $current_sha) {
            update_option('ap_update_available', 1);
            update_option('ap_update_remote_sha', $remote_sha);
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_available', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return true;
        }
        update_option('ap_update_available', 0);
        if (!$silent) {
            wp_safe_redirect(add_query_arg('update_checked', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
            exit;
        }
        return false;
    }

    public static function run_update(bool $silent = false): void
    {
        if (!current_user_can('manage_options')) {
            if ($silent) {
                return;
            }
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        $info = self::get_repo_info();
        [$owner, $repo] = self::parse_repo($info['url']);
        if (empty($owner) || empty($repo)) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return;
        }
        $zip = "https://codeload.github.com/{$owner}/{$repo}/zip/refs/heads/{$info['branch']}";
        $args = [
            'headers' => [
                'User-Agent' => 'ArtPulse-Updater',
            ],
        ];
        if (!empty($info['token'])) {
            $args['headers']['Authorization'] = 'token ' . $info['token'];
        }
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $tmp = download_url($zip, 300, '', $args);
        if (is_wp_error($tmp)) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return;
        }
        $plugin_dir = plugin_dir_path(ARTPULSE_PLUGIN_FILE);
        $result = unzip_file($tmp, $plugin_dir);
        unlink($tmp);
        if (is_wp_error($result)) {
            if (!$silent) {
                wp_safe_redirect(add_query_arg('update_error', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
                exit;
            }
            return;
        }
        update_option('ap_current_repo_sha', get_option('ap_update_remote_sha'));
        update_option('ap_last_update_time', current_time('mysql'));
        if (!$silent) {
            wp_safe_redirect(add_query_arg('update_success', '1', wp_get_referer() ?: admin_url('admin.php?page=artpulse-settings#updates')));
            exit;
        }
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        $last_check  = get_option('ap_update_last_check');
        $last_update = get_option('ap_last_update_time');
        ?>
        <h2><?php esc_html_e('Manual Update', 'artpulse'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('ap_check_updates'); ?>
            <input type="hidden" name="action" value="ap_check_updates" />
            <button type="submit" class="button"><?php esc_html_e('Check for Updates', 'artpulse'); ?></button>
        </form>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:10px;">
            <?php wp_nonce_field('ap_run_update'); ?>
            <input type="hidden" name="action" value="ap_run_update" />
            <button type="submit" class="button button-primary"><?php esc_html_e('Update Now', 'artpulse'); ?></button>
        </form>
        <p style="margin-top:10px;">
            <?php if ($last_check) : ?>
                <?php esc_html_e('Last Checked:', 'artpulse'); ?> <?php echo esc_html($last_check); ?><br />
            <?php endif; ?>
            <?php if ($last_update) : ?>
                <?php esc_html_e('Last Updated:', 'artpulse'); ?> <?php echo esc_html($last_update); ?>
            <?php endif; ?>
        </p>
        <?php
    }
}
