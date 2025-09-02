<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

class OrgRolesController {
    use RestResponder;

    public static function register(): void {
        $controller = new self();
        if ( did_action( 'rest_api_init' ) ) {
            $controller->register_routes();
        } else {
            add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
        }
    }

    public function register_routes(): void {
        register_rest_route(
            ARTPULSE_API_NAMESPACE,
            '/roles',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_roles' ),
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            )
        );
    }

    public function get_roles( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_roles';
        $rows  = $wpdb->get_results( "SELECT role_key, display_name, parent_role_key FROM $table", ARRAY_A );
        if ( ! is_array( $rows ) ) {
            $rows = array();
        }
        return $this->ok( $rows );
    }
}
