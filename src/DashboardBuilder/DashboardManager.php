<?php
namespace ArtPulse\DashboardBuilder;

/**
 * Registers the Dashboard Builder admin page and enqueues the React app.
 */
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetVisibility;

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
        self::render_builder();
    }

    /**
     * Output the Dashboard Builder interface markup.
     *
     * @param bool $include_heading Whether to include the page heading.
     */
    public static function render_builder(bool $include_heading = true): void {
        echo '<div id="ap-dashboard-builder"' . ($include_heading ? ' class="wrap"' : '') . '>';
        if ($include_heading) {
            echo '<h1>' . esc_html__('Dashboard Builder', 'artpulse') . '</h1>';
        }
        echo '<label for="ap-db-role" class="screen-reader-text">' . esc_html__('Select Role', 'artpulse') . '</label>';
        echo '<select id="ap-db-role"></select>';
        echo '<label style="margin-left:10px"><input type="checkbox" id="ap-db-show-all"> ' . esc_html__('Show All Widgets', 'artpulse') . '</label>';
        echo '<div id="ap-db-filters" style="margin-top:10px">';
        echo '<label><input type="checkbox" id="ap-db-filter-public" class="ap-db-filter" value="'. WidgetVisibility::PUBLIC.'" checked> ' . esc_html__('Public', 'artpulse') . '</label>';
        echo '<label style="margin-left:10px"><input type="checkbox" id="ap-db-filter-internal" class="ap-db-filter" value="'. WidgetVisibility::INTERNAL.'"> ' . esc_html__('Internal', 'artpulse') . '</label>';
        echo '<label style="margin-left:10px"><input type="checkbox" id="ap-db-filter-deprecated" class="ap-db-filter" value="'. WidgetVisibility::DEPRECATED.'"> ' . esc_html__('Deprecated', 'artpulse') . '</label>';
        echo '</div>';
        echo '<ul id="ap-db-layout"></ul>';
        echo '<ul id="ap-db-available" class="ap-available"></ul>';
        echo '<p><button id="ap-db-save" class="button button-primary">' . esc_html__('Save Changes', 'artpulse') . '</button></p>';
        echo '<p id="ap-db-warning" style="display:none" class="notice notice-warning"></p>';
        echo '</div>';
    }

    public static function enqueue(string $hook): void {
        if ($hook === 'toplevel_page_dashboard-builder') {
            self::enqueue_assets();
        }
    }

    /**
     * Enqueue scripts and styles for the builder.
     */
    public static function enqueue_assets(): void {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
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
        foreach (DashboardWidgetRegistry::get_all(null, true) as $def) {
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
            'rest_root'  => esc_url_raw(rest_url()),
            'nonce'      => wp_create_nonce('wp_rest'),
            'roles'      => $roles,
            'debug'      => defined('WP_DEBUG') && WP_DEBUG,
            'visibility' => WidgetVisibility::all(),
        ]);
    }
}
