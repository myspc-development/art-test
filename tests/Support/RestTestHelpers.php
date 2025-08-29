<?php
namespace ArtPulse\Tests;

function ap_tests_boot_rest_defaults(): void {
    do_action('rest_api_init');
    $admin_id = username_exists('admin');
    if (!$admin_id) {
        $admin_id = wp_create_user('admin', 'password', 'admin@example.com');
        $user = new \WP_User($admin_id);
        $user->set_role('administrator');
    }
    wp_set_current_user($admin_id);
}
