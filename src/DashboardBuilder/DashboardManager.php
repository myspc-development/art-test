<?php
namespace ArtPulse\DashboardBuilder;

/**
 * Registers the Dashboard Builder admin page and enqueues the React app.
 */
use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

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
        echo '<div class="wrap" id="ap-dashboard-builder">
            <h1>' . esc_html__('Dashboard Builder', 'artpulse') . '</h1>
            <label for="ap-db-role" class="screen-reader-text">' . esc_html__('Select Role', 'artpulse') . '</label>
            <select id="ap-db-role"></select>
            <label style="margin-left:10px"><input type="checkbox" id="ap-db-show-all"> ' . esc_html__('Show All Widgets', 'artpulse') . '</label>
            <ul id="ap-db-layout"></ul>
            <ul id="ap-db-available" class="ap-available"></ul>
            <p><button id="ap-db-save" class="button button-primary">' . esc_html__('Save Changes', 'artpulse') . '</button></p>
            <p id="ap-db-warning" style="display:none" class="notice notice-warning"></p>
        </div>';
    }

    public static function enqueue(string $hook): void {
        if ($hook !== 'toplevel_page_dashboard-builder') {
            return;
        }
        wp_enqueue_script(
            'ap-dashboard-builder',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/dashboard-builder.js',
            ['jquery', 'jquery-ui-sortable'],
            ARTPULSE_VERSION,
            true
        );
        wp_enqueue_style(
            'ap-dashboard-builder',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/css/dashboard-builder.css',
            [],
            ARTPULSE_VERSION
        );
        $widget_roles = [];
        foreach (DashboardWidgetRegistry::get_all() as $def) {
            foreach ((array)($def['roles'] ?? []) as $r) {
                $widget_roles[$r] = true;
            }
        }
        $roles = array_keys($widget_roles);
        sort($roles);
        if (!$roles) {
            $roles = array_values(array_keys(get_editable_roles()));
        }
        wp_localize_script('ap-dashboard-builder', 'APDashboardBuilder', [
            'rest_root' => esc_url_raw(rest_url()),
            'nonce'     => wp_create_nonce('wp_rest'),
            'roles'     => $roles,
        ]);
    }
}
