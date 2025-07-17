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
        function () {
            include __DIR__ . '/page-org-reports.php';
        }
    );

    // Organization CRM dashboard
    add_menu_page(
        'CRM',
        'CRM',
        'manage_options',
        'ap-org-crm',
        function () {
            include __DIR__ . '/page-org-crm.php';
        }
    );

    // Partner API key management
    add_menu_page(
        'API Keys',
        'API Keys',
        'manage_options',
        'ap-api-keys',
        function () {
            include __DIR__ . '/page-api-keys.php';
        }
    );
});

// Redirect old Roles Matrix slug to the modern page
add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'ap-org-roles-matrix') {
        wp_safe_redirect(admin_url('admin.php?page=ap-org-roles'));
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
    } elseif ($hook === 'toplevel_page_ap-report-templates') {
        wp_enqueue_script(
            'ap-report-template-editor',
            plugin_dir_url(__FILE__) . '../assets/js/report-template-editor.js',
            ['jquery'],
            false,
            true
        );
        wp_localize_script('ap-report-template-editor', 'wpApiSettings', [
            'root'  => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
});
