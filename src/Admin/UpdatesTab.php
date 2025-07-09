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
        $result = self::check_updates(true);
        if ($result === true) {
            self::run_update(true);
        } elseif (is_wp_error($result)) {
            error_log('🔧 Update check failed: ' . $result->get_error_message());
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

    private static function redirect_to_updates(array $params = []): void
    {
        $url = admin_url('admin.php?page=artpulse-settings');
        if ($params) {
            $url = add_query_arg($params, $url);
        }
        wp_safe_redirect($url . '#updates');
        exit;
    }

    /**
     * Check if an update is available.
     *
     * @param bool $silent Whether to suppress redirects/messages.
     * @return bool|\WP_Error True if update available, false if not, WP_Error on failure.
     */
    public static function check_updates(bool $silent = false)
    {
        if (!current_user_can('manage_options')) {
            if ($silent) {
                return false;
            }
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        if (!$silent) {
            check_admin_referer('ap_check_updates');
        }
        $info = self::get_repo_info();
        if (empty($info['url'])) {
            $err = new \WP_Error('missing_repo', 'Repository URL not configured');
            if (!$silent) {
                self::redirect_to_updates(['ap_update_error' => urlencode($err->get_error_message())]);
            }
            return $err;
        }
        [$owner, $repo] = self::parse_repo($info['url']);
        if (empty($owner) || empty($repo)) {
            $err = new \WP_Error('invalid_repo', 'Invalid repository URL');
            if (!$silent) {
                self::redirect_to_updates(['ap_update_error' => urlencode($err->get_error_message())]);
            }
            return $err;
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
        error_log('🔧 GitHub response: ' . print_r($response, true));
        if (is_wp_error($response)) {
            if (!$silent) {
                self::redirect_to_updates(['ap_update_error' => urlencode($response->get_error_message())]);
            }
            return $response;
        }
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($body['sha'])) {
            $err = new \WP_Error('invalid_response', 'Invalid response from GitHub');
            if (!$silent) {
                self::redirect_to_updates(['ap_update_error' => urlencode($err->get_error_message())]);
            }
            return $err;
        }
        $remote_sha = $body['sha'];
        $current_sha = get_option('ap_current_repo_sha');
        update_option('ap_update_last_check', current_time('mysql'));
        if ($remote_sha !== $current_sha) {
            update_option('ap_update_available', 1);
            update_option('ap_update_remote_sha', $remote_sha);
            if (!$silent) {
                self::redirect_to_updates(['update_available' => '1']);
            }
            return true;
        }
        update_option('ap_update_available', 0);
        if (!$silent) {
            self::redirect_to_updates(['update_checked' => '1']);
        }
        return false;
    }

    /**
     * Perform the plugin update.
     *
     * @param bool $silent Whether to suppress redirects/messages.
     * @return bool|\WP_Error True on success, WP_Error on failure.
     */
    public static function run_update(bool $silent = false)
    {
        if (!current_user_can('manage_options')) {
            if ($silent) {
                return new \WP_Error('permission_denied', 'Insufficient permissions');
            }
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        if (!$silent) {
            check_admin_referer('ap_run_update');
        }
        error_log('🔧 Starting update...');
        $info = self::get_repo_info();
        [$owner, $repo] = self::parse_repo($info['url']);
        if (empty($owner) || empty($repo)) {
            $err = new \WP_Error('invalid_repo', 'Invalid repository URL');
            if (!$silent) {
                self::redirect_to_updates(['ap_update_error' => urlencode($err->get_error_message())]);
            }
            return $err;
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
                $msg = $tmp->get_error_message();
                error_log('🔧 Update failed: ' . print_r($tmp, true));
                self::redirect_to_updates(['ap_update_error' => urlencode($msg)]);
            }
            return $tmp;
        }
        $plugin_dir = plugin_dir_path(ARTPULSE_PLUGIN_FILE);
        $temp_dir   = trailingslashit(get_temp_dir()) . 'ap_update_' . wp_generate_password(8, false);
        wp_mkdir_p($temp_dir);
        $result = unzip_file($tmp, $temp_dir);
        $files  = [];
        if (!is_wp_error($result)) {
            $zip = new \ZipArchive();
            if ($zip->open($tmp) === true) {
                // Gather a flat list of files from the archive.
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (!str_ends_with($name, '/')) {
                        $files[] = $name;
                    }
                }
                $zip->close();
            }
        }
        if (!is_wp_error($result)) {
            $entries = array_values(array_filter(scandir($temp_dir), function ($e) { return $e !== '.' && $e !== '..'; }));
            $src = $temp_dir;
            if (count($entries) === 1 && is_dir($temp_dir . '/' . $entries[0])) {
                $src = $temp_dir . '/' . $entries[0];
            }
            self::copy_recursive($src, $plugin_dir);
        }
        self::delete_recursive($temp_dir);
        unlink($tmp);
        if (is_wp_error($result)) {
            if (!$silent) {
                $msg = $result->get_error_message();
                error_log('🔧 Update failed: ' . print_r($result, true));
                self::redirect_to_updates(['ap_update_error' => urlencode($msg)]);
            }
            return $result;
        }
        update_option('ap_current_repo_sha', get_option('ap_update_remote_sha'));
        update_option('ap_last_update_time', current_time('mysql'));
        update_option('ap_updated_files', $files);
        if (!$silent) {
            self::redirect_to_updates(['ap_update_success' => '1']);
        }
        return true;
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        if (isset($_GET['ap_update_success']) && $_GET['ap_update_success'] === '1') {
            echo '<div class="notice notice-success"><p>✅ Plugin updated successfully.</p></div>';
            $files = get_option('ap_updated_files', []);
            if ($files) {
                echo '<ul style="margin-top:10px;">';
                foreach ($files as $f) {
                    echo '<li>' . esc_html($f) . '</li>';
                }
                echo '</ul>';
            }
            delete_option('ap_updated_files');
        }

        if (isset($_GET['ap_update_error'])) {
            echo '<div class="notice notice-error"><p>❌ Update failed: ' . esc_html($_GET['ap_update_error']) . '</p></div>';
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
            <button type="submit" id="ap-update-btn" class="button button-primary">
                <?php esc_html_e('Update Now', 'artpulse'); ?>
                <span class="spinner" style="float: none; margin-left: 6px;"></span>
            </button>
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

    private static function copy_recursive(string $src, string $dest): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $target = $dest . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    private static function delete_recursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($dir);
    }
}
