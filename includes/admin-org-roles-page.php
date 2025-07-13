<?php
if (!defined('ABSPATH')) { exit; }


function ap_render_org_roles_page() {
    echo '<div id="ap-org-roles-root"></div>';
}

add_action('admin_menu', function () {
    $cap = current_user_can('view_artpulse_dashboard') ? 'view_artpulse_dashboard' : 'manage_options';
    add_submenu_page(
        'ap-org-dashboard',
        __('Roles & Permissions', 'artpulse'),
        __('Roles & Permissions', 'artpulse'),
        $cap,
        'ap-org-roles',
        'ap_render_org_roles_page'
    );
});

add_action('admin_init', function () {
    $uri  = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($uri, PHP_URL_PATH);
    if ($path === '/wp-admin/ap-org-roles') {
        wp_safe_redirect(admin_url('admin.php?page=ap-org-roles'));
        exit;
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'ap-org-dashboard_page_ap-org-roles') {
        return;
    }

    $script_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-org-roles.js';

    wp_enqueue_script(
        'ap-org-roles',
        plugins_url('assets/js/ap-org-roles.js', ARTPULSE_PLUGIN_FILE),
        ['wp-element', 'wp-api-fetch'],
        filemtime($script_path),
        true
    );

    wp_localize_script('ap-org-roles', 'ArtPulseOrgRoles', [
        'api_url' => rest_url('artpulse/v1/org-roles'),
        'nonce'   => wp_create_nonce('wp_rest')
    ]);
});
