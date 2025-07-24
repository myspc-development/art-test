<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\OrgRoleManager;
use ArtPulse\Core\RoleAuditLogger;
use ArtPulse\Core\OrgContext;

class OrgRolesPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_post_ap_save_org_roles', [self::class, 'handleForm']);
        add_action('admin_init', [self::class, 'maybe_redirect_slug']);
    }

    public static function addMenu(): void
    {
        $capability = current_user_can('view_artpulse_dashboard')
            ? 'view_artpulse_dashboard'
            : 'manage_options';

        add_submenu_page(
            'ap-org-dashboard',
            __('Roles & Permissions', 'artpulse'),
            __('Roles & Permissions', 'artpulse'),
            $capability,
            'ap-org-roles',
            'ap_render_org_roles_page'
        );
    }

    public static function maybe_redirect_slug(): void
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH);
        if ($path === '/wp-admin/ap-org-roles' || $path === '/wp-admin/ap-org-roles-matrix') {
            wp_safe_redirect(admin_url('admin.php?page=ap-org-roles'));
            exit;
        }
    }

    private static function get_current_org_id(): int
    {
        return OrgContext::get_current_org_id();
    }

    public static function render(): void
    {
        if (!current_user_can('view_artpulse_dashboard') && !current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        $org_id = self::get_current_org_id();
        $roles  = OrgRoleManager::get_roles($org_id);
        $caps   = OrgRoleManager::ALL_CAPABILITIES;
        echo '<div class="wrap"><h1>' . esc_html__('Roles & Permissions', 'artpulse') . '</h1>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('ap_save_org_roles');
        echo '<input type="hidden" name="action" value="ap_save_org_roles" />';
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('Role', 'artpulse') . '</th><th>' . esc_html__('Permissions', 'artpulse') . '</th></tr></thead><tbody>';
        foreach ($roles as $key => $data) {
            $perms = (array) ($data['caps'] ?? $data);
            echo '<tr><td><input type="text" name="roles[' . esc_attr($key) . '][name]" value="' . esc_attr($data['name'] ?? $key) . '" /></td><td>';
            foreach ($caps as $cap) {
                $checked = in_array($cap, $perms, true) ? 'checked' : '';
                echo '<label><input type="checkbox" name="roles[' . esc_attr($key) . '][caps][]" value="' . esc_attr($cap) . '" ' . $checked . ' /> ' . esc_html($cap) . '</label>';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<p><button class="button button-primary" type="submit">' . esc_html__('Save Roles', 'artpulse') . '</button></p>';
        echo '</form></div>';
    }

    public static function handleForm(): void
    {
        if (!current_user_can('view_artpulse_dashboard') && !current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        check_admin_referer('ap_save_org_roles');
        $org_id = self::get_current_org_id();
        $raw    = $_POST['roles'] ?? [];
        $roles  = [];
        foreach ($raw as $key => $r) {
            $caps = array_map('sanitize_key', (array) ($r['caps'] ?? []));
            $name = sanitize_text_field($r['name'] ?? $key);
            $roles[sanitize_key($key)] = ['name' => $name, 'caps' => array_values(array_unique($caps))];
        }
        $old = OrgRoleManager::get_roles($org_id);
        OrgRoleManager::save_roles($org_id, $roles);
        RoleAuditLogger::log($org_id, 0, get_current_user_id(), $old, $roles);
        wp_redirect(admin_url('admin.php?page=ap-org-roles&updated=1'));
        exit;
    }
}

