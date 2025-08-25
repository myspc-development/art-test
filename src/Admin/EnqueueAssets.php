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
        if (wp_style_is($handle, 'enqueued')) {
            return;
        }
        $path = self::asset_path($rel);
        if (file_exists($path)) {
            wp_enqueue_style($handle, self::asset_url($rel), $deps, filemtime($path));
        }
    }

    /**
     * Enqueue a script only if the file exists and has not been enqueued.
     */
    private static function enqueue_script_if_exists(string $handle, string $rel, array $deps = [], bool $in_footer = true): void {
        if (wp_script_is($handle, 'enqueued')) {
            return;
        }
        $path = self::asset_path($rel);
        if (file_exists($path)) {
            wp_enqueue_script($handle, self::asset_url($rel), $deps, filemtime($path), $in_footer);
        }
    }

    /**
     * Register Chart.js so it can be used as a dependency.
     */
    private static function register_chart_js(): void {
        $rel  = 'assets/libs/chart.js/4.4.1/chart.min.js';
        $path = self::asset_path($rel);
        if (!wp_script_is('chart-js', 'registered') && file_exists($path)) {
            wp_register_script('chart-js', self::asset_url($rel), [], filemtime($path), true);
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
        self::enqueue_style_if_exists('artpulse-editor-styles', 'assets/css/editor.css');
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

        $tab = sanitize_text_field(wp_unslash($_GET['tab'] ?? ''));

        if ($tab === 'import_export') {
            self::enqueue_script_if_exists('papaparse', 'assets/libs/papaparse/papaparse.min.js');
            self::enqueue_script_if_exists('ap-csv-import', 'assets/js/ap-csv-import.js', ['papaparse', 'wp-api-fetch']);
        }

        self::enqueue_script_if_exists('ap-analytics', 'assets/js/ap-analytics.js');
        self::enqueue_script_if_exists('ap-user-dashboard-js', 'assets/js/ap-user-dashboard.js', ['wp-api-fetch', 'chart-js']);
    }

    /**
     * Front-end asset loading.
     */
    public static function enqueue_frontend(): void {
        self::register_chart_js();
        // Frontend scripts would be enqueued here when needed.
    }
}

