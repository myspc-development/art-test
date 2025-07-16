<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_menu_page('Inbox', 'Inbox', 'read', 'ap-user-inbox', function () {
        include __DIR__ . '/page-user-inbox.php';
    });
    // Simple organization reports page for exporting budgets
    add_menu_page(
        'Organization Reports',
        'Org Reports',
        'manage_options',
        'ap-org-reports',
        function () { include __DIR__ . '/page-org-reports.php'; }
    );

    // Partner API key management
    add_menu_page(
        'API Keys',
        'API Keys',
        'manage_options',
        'ap-api-keys',
        function () { include __DIR__ . '/page-api-keys.php'; }
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

