<?php
namespace ArtPulse\Admin;

class DashboardWidgetSettingsPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu']);
        add_action('admin_post_ap_save_widget_access', [self::class, 'handle_save']);
    }

    public static function add_menu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Widget Access', 'artpulse'),
            __('Widget Access', 'artpulse'),
            'manage_options',
            'ap-dashboard-widget-access',
            [self::class, 'render_settings_page']
        );
    }

    public static function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        $widgets   = DashboardWidgetTools::get_available_widgets();
        $roles     = wp_roles()->roles;
        $settings  = get_option('artpulse_dashboard_widgets_by_role', []);

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Dashboard Widget Access', 'artpulse') . '</h1>';
        if (isset($_GET['updated'])) {
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved.', 'artpulse') . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('ap_save_widget_access');
        echo '<input type="hidden" name="action" value="ap_save_widget_access" />';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>' . esc_html__('Widget', 'artpulse') . '</th>';
        foreach ($roles as $role => $data) {
            echo '<th>' . esc_html($data['name']) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($widgets as $id => $info) {
            echo '<tr><td>' . esc_html($info['title']) . '</td>';
            foreach ($roles as $role => $data) {
                $checked = '';
                if (isset($settings[$role]) && in_array($id, (array) $settings[$role], true)) {
                    $checked = 'checked';
                }
                echo '<td style="text-align:center">';
                echo '<input type="checkbox" name="widgets[' . esc_attr($role) . '][]" value="' . esc_attr($id) . '" ' . $checked . ' />';
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        submit_button(__('Save Changes', 'artpulse'));
        echo '</form></div>';
    }

    public static function handle_save(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        check_admin_referer('ap_save_widget_access');

        $raw   = $_POST['widgets'] ?? [];
        $data  = [];
        foreach ($raw as $role => $ids) {
            $role_key = sanitize_key($role);
            $list     = [];
            foreach ((array) $ids as $id) {
                $list[] = sanitize_key($id);
            }
            $data[$role_key] = $list;
        }
        update_option('artpulse_dashboard_widgets_by_role', $data);
        wp_safe_redirect(admin_url('admin.php?page=ap-dashboard-widget-access&updated=1'));
        exit;
    }
}
