<?php
namespace ArtPulse\DashboardBuilder;

/**
 * Registers the Dashboard Builder admin page and enqueues the React app.
 */
class DashboardManager {
    public static function register(): void {
        add_action('admin_menu', [self::class, 'add_menu']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function add_menu(): void {
        add_menu_page(
            __('Dashboard Builder', 'artpulse'),
            __('Dashboard Builder', 'artpulse'),
            'manage_options',
            'dashboard-builder',
            [self::class, 'render_page'],
            'dashicons-screenoptions',
            58
        );
    }

    public static function render_page(): void {
        echo '<div class="wrap"><div id="dashboard-builder-root"></div></div>';
    }

    public static function enqueue(string $hook): void {
        if ($hook !== 'toplevel_page_dashboard-builder') {
            return;
        }
        wp_enqueue_script(
            'ap-dashboard-builder',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'dist/dashboard-builder.js',
            ['wp-element', 'wp-components'],
            ARTPULSE_VERSION,
            true
        );
        wp_localize_script('ap-dashboard-builder', 'APDashboardBuilder', [
            'rest_root' => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce('wp_rest'),
            'roles'     => array_values(array_keys(get_editable_roles())),
        ]);
    }
}
