<?php
namespace ArtPulse\Admin;

// Handles asset loading across admin, editor, and frontend
class EnqueueAssets {

    /**
     * Build an absolute path to a plugin asset.
     */
    private static function asset_path(string $rel): string {
        $rel = ltrim(trim($rel), '/');
        $rel = str_replace('..', '', $rel);
        return rtrim(plugin_dir_path(ARTPULSE_PLUGIN_FILE), '/\\') . '/' . $rel;
    }

    /**
     * Build a URL to a plugin asset.
     */
    private static function asset_url(string $rel): string {
        $rel = ltrim(trim($rel), '/');
        $rel = str_replace('..', '', $rel);
        return rtrim(plugin_dir_url(ARTPULSE_PLUGIN_FILE), '/\\') . '/' . $rel;
    }

    /**
     * Enqueue a style only if the file exists and has not been enqueued.
     */
    private static function enqueue_style_if_exists(string $handle, string $rel, array $deps = []): void {
        if (function_exists('wp_style_is') && wp_style_is($handle, 'enqueued')) {
            return;
        }
        $path = self::asset_path($rel);
        if (file_exists($path) && function_exists('wp_enqueue_style')) {
            wp_enqueue_style($handle, self::asset_url($rel), $deps, filemtime($path));
        }
    }

    /**
     * Enqueue a script only if the file exists and has not been enqueued.
     */
    private static function enqueue_script_if_exists(string $handle, string $rel, array $deps = [], bool $in_footer = true, array $attributes = []): void {
        if (function_exists('wp_script_is') && wp_script_is($handle, 'enqueued')) {
            return;
        }
        $path = self::asset_path($rel);
        if (file_exists($path) && function_exists('wp_enqueue_script')) {
            wp_enqueue_script($handle, self::asset_url($rel), $deps, filemtime($path), $in_footer);
            if ($attributes && function_exists('wp_script_add_data')) {
                foreach ($attributes as $key => $value) {
                    wp_script_add_data($handle, $key, $value);
                }
            }
        }
    }

    /**
     * Register Chart.js so it can be used as a dependency.
     */
    private static function register_chart_js(): void {
        if (function_exists('wp_script_is') && wp_script_is('chart-js', 'registered')) {
            return;
        }

        $rel  = 'assets/libs/chart.js/4.4.1/chart.min.js';
        $path = self::asset_path($rel);
        $ver  = file_exists($path) ? filemtime($path) : '4.4.1';

        if (function_exists('wp_register_script')) {
            wp_register_script('chart-js', self::asset_url($rel), [], $ver, true);
        }
    }

    /**
     * Wire up WordPress hooks.
     */
    public static function register(): void {
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_block_editor_assets']);
        // There is no enqueue_block_editor_styles hook.
        add_action('enqueue_block_editor_assets', [self::class, 'enqueue_block_editor_styles']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend']);
    }

    /**
     * Editor scripts.
     */
    public static function enqueue_block_editor_assets(): void {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || (method_exists($screen, 'is_block_editor') && !$screen->is_block_editor())) {
            return;
        }
        // Placeholder for future editor scripts.
    }

    /**
     * Editor styles loaded via enqueue_block_editor_assets.
     */
    public static function enqueue_block_editor_styles(): void {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || (method_exists($screen, 'is_block_editor') && !$screen->is_block_editor())) {
            return;
        }
        self::enqueue_style_if_exists('artpulse-editor-styles', 'assets/css/editor-styles.css');
    }

    /**
     * Admin side asset loading.
     */
    public static function enqueue_admin(string $hook): void {
        self::register_chart_js();

        $dashboard_pages = ['toplevel_page_ap-dashboard', 'toplevel_page_ap-org-dashboard'];
        if (in_array($hook, $dashboard_pages, true)) {
            self::enqueue_style_if_exists('ap-dashboard', 'assets/css/dashboard.css');
            self::enqueue_script_if_exists('ap-role-tabs', 'assets/js/dashboard-role-tabs.js');

            $deps = ['ap-role-tabs'];
            $sortable_rel = 'assets/libs/sortablejs/Sortable.min.js';
            if (file_exists(self::asset_path($sortable_rel))) {
                self::enqueue_script_if_exists('sortablejs', $sortable_rel);
                if (wp_script_is('sortablejs', 'enqueued')) {
                    $deps[] = 'sortablejs';
                }
            }

            self::enqueue_script_if_exists('role-dashboard', 'assets/js/role-dashboard.js', $deps);
            return;
        }

        $settings_pages = [
            'toplevel_page_artpulse-settings',
            'artpulse-settings_page_artpulse-import-export',
            'artpulse-settings_page_artpulse-quickstart',
            'artpulse-settings_page_artpulse-engagement',
        ];
        if (!in_array($hook, $settings_pages, true)) {
            return;
        }

        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';

        if ($tab === 'import_export') {
            self::enqueue_script_if_exists('papaparse', 'assets/libs/papaparse/papaparse.min.js');
            self::enqueue_script_if_exists('ap-csv-import', 'assets/js/ap-csv-import.js', ['papaparse', 'wp-api-fetch']);
        }

        self::enqueue_script_if_exists('ap-analytics', 'assets/js/ap-analytics.js', [], true, ['type' => 'module']);
        self::enqueue_script_if_exists('ap-user-dashboard-js', 'assets/js/ap-user-dashboard.js', ['wp-api-fetch', 'chart-js'], true, ['type' => 'module']);
    }

    /**
     * Front-end asset loading.
     */
    public static function enqueue_frontend(): void {
        self::register_chart_js();

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        // Guard helper existence
        if (!function_exists('ap_styles_disabled') || !ap_styles_disabled()) {
            wp_enqueue_style(
                'ap-style',
                $plugin_url . '/assets/css/ap-style.css',
                [],
                '1.0.0'
            );
        }
        if (function_exists('ap_enqueue_frontend_styles')) {
            ap_enqueue_frontend_styles();
        }

        // Frontend scripts would be enqueued here when needed.
    }
}

