<?php
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function () {
    add_menu_page(
        'Organization Roles',
        'Org Roles',
        'manage_options',
        'ap-org-roles',
        'ap_render_org_roles_page',
        'dashicons-groups',
        60
    );
});

function ap_render_org_roles_page() {
    echo '<div id="ap-org-roles-root"></div>';
}

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_ap-org-roles') {
        return;
    }

    wp_enqueue_script(
        'ap-org-roles',
        plugins_url('assets/js/ap-org-roles.js', ARTPULSE_PLUGIN_FILE),
        ['wp-element', 'wp-api-fetch'],
        '1.0.0',
        true
    );

    wp_localize_script('ap-org-roles', 'ArtPulseOrgRoles', [
        'api_url' => rest_url('artpulse/v1/org-roles'),
        'nonce'   => wp_create_nonce('wp_rest')
    ]);
});
