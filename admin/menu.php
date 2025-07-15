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
    if ($hook === 'toplevel_page_ap-org-roles-matrix') {
        wp_enqueue_script(
            'ap-role-matrix-bundle',
            plugin_dir_url(__FILE__) . '../dist/role-matrix.js',
            ['wp-element'],
            filemtime(plugin_dir_path(__FILE__) . '../dist/role-matrix.js'),
            true
        );
    }
});
