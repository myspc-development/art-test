<?php
if (!defined('ABSPATH')) { exit; }


function ap_render_org_roles_page() {
    echo '<div id="ap-org-roles-root"></div>';
}

add_action('admin_menu', function () {
    add_submenu_page(
        'ap-org-dashboard',
        'Roles Matrix',
        'Roles Matrix',
        'manage_options',
        'ap-org-roles-matrix',
        'ap_render_org_roles_page'
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'ap-org-dashboard_page_ap-org-roles-matrix') {
        return;
    }

    $script_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-org-roles.bundle.js';

    wp_enqueue_script(
        'ap-org-roles',
        plugins_url('assets/js/ap-org-roles.bundle.js', ARTPULSE_PLUGIN_FILE),
        ['wp-element', 'wp-api-fetch'],
        filemtime($script_path),
        true
    );

    wp_localize_script('ap-org-roles', 'ArtPulseOrgRoles', [
        'api_path' => 'artpulse/v1/org-roles',
        'nonce'    => wp_create_nonce('wp_rest'),
    ]);
    wp_localize_script('ap-org-roles', 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
});
