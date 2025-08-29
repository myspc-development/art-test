<?php
namespace ArtPulse\Tests;

final class RestTestHelpers {
    public static function boot(): void {
        if ( ! class_exists( 'Spy_REST_Server' ) ) {
            require_once __DIR__ . '/../../vendor/wp-phpunit/wp-phpunit/includes/spy-rest-server.php';
        }
        do_action('rest_api_init');
        $admin_id = username_exists('admin');
        if (!$admin_id) {
            $admin_id = wp_create_user('admin', 'password', 'admin@example.com');
            $user = new \WP_User($admin_id);
            $user->set_role('administrator');
        }
        wp_set_current_user($admin_id);
    }
}
