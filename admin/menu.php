<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_menu_page('Inbox', 'Inbox', 'read', 'ap-user-inbox', function () {
        include __DIR__ . '/page-user-inbox.php';
    });

    add_submenu_page(
        'ap-user-inbox',
        __('Org Roles', 'artpulse'),
        __('Org Roles', 'artpulse'),
        'manage_options',
        'ap-org-roles',
        function () { include __DIR__ . '/page-org-roles.php'; }
    );

    add_menu_page(
        'Org Role Matrix',
        'Org Role Matrix',
        'manage_options',
        'ap-org-roles-matrix',
        function () { include __DIR__ . '/page-org-roles-matrix.php'; }
    );

    add_menu_page('Update Diagnostics', 'Update Status', 'manage_options', 'ap-update-diagnostics', function () {
        include __DIR__ . '/page-update-diagnostics.php';
    });
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_ap-user-inbox') {
        wp_enqueue_script('ap-messages-js', plugin_dir_url(__FILE__) . '../assets/js/messages.js', [], false, true);
    }
    if ($hook === 'toplevel_page_ap-org-roles-matrix') {
        wp_enqueue_script('ap-role-matrix-bundle', plugin_dir_url(__FILE__) . '../dist/role-matrix.js', ['react', 'react-dom'], filemtime(plugin_dir_path(__FILE__) . '../dist/role-matrix.js'), true);
    }
    if ($hook === 'toplevel_page_ap-update-diagnostics') {
        wp_enqueue_script('ap-update-diagnostics', plugin_dir_url(__FILE__) . '../assets/js/update-diagnostics.js', [], false, true);
    }
});
