<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_menu_page('Inbox', 'Inbox', 'read', 'ap-user-inbox', function () {
        include __DIR__ . '/page-user-inbox.php';
    });

    // Org Roles now lives under the Org Role Matrix page as a tab.
    add_menu_page(
        'Org Role Matrix',
        'Org Role Matrix',
        'manage_options',
        'ap-org-roles-matrix',
        function () { include __DIR__ . '/page-org-roles-matrix.php'; }
    );

});

// Redirect legacy Org Roles slug to the new tabbed page
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'ap-org-roles') {
        wp_safe_redirect(admin_url('admin.php?page=ap-org-roles-matrix&view=roles'));
        exit;
    }
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_ap-user-inbox') {
        wp_enqueue_script(
            'ap-messages-js',
            plugin_dir_url(__FILE__) . '../assets/js/messages.js',
            ['wp-api-fetch'],
            false,
            true
        );
        wp_localize_script('ap-messages-js', 'wpApiSettings', [
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
});

/**
 * Enqueue Org‑Roles Matrix assets
 */
function ap_enqueue_org_roles_assets($hook)
{
    // Only load on the Org‑Roles Matrix admin page
    if ($hook !== 'toplevel_page_ap-org-roles-matrix') {
        return;
    }

    $handle    = 'ap-org-roles-bundle';
    $src       = plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-org-roles.bundle.js';
    $deps      = [
        'wp-element',
        'wp-i18n',
        'wp-api-fetch',
        'wp-components',
        'wp-data',
    ];
    $ver       = filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-org-roles.bundle.js');
    $in_footer = true;

    wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);

    // Pass REST url + nonce to the bundle
    $current_org = absint(get_user_meta(get_current_user_id(), 'ap_organization_id', true));
    wp_localize_script($handle, 'ArtPulseOrgRoles', [
        'base'   => 'artpulse/v1',
        'nonce'  => wp_create_nonce('wp_rest'),
        'orgId'  => $current_org,
    ]);
    wp_localize_script($handle, 'wpApiSettings', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);

    // Optional: enqueue CSS for the matrix UI
    $css_path = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/org-roles.css';
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'ap-org-roles-style',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/css/org-roles.css',
            [],
            filemtime($css_path)
        );
    }
}
add_action('admin_enqueue_scripts', 'ap_enqueue_org_roles_assets');
