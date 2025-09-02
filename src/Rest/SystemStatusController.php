<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Rest\RestResponder;

final class SystemStatusController {
        use RestResponder;

        public static function register(): void {
                $controller = new self();
                if ( did_action( 'rest_api_init' ) ) {
                        $controller->routes();
                } else {
                        add_action( 'rest_api_init', array( $controller, 'routes' ) );
                }
        }

       public function routes(): void {
                register_rest_route(
                        'ap/v1',
                        '/system/status',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                                'permission_callback' => '__return_true',
                                'callback'            => array( $this, 'get_status' ),
                        )
                );
                register_rest_route(
                        'ap/v1',
                        '/status',
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                                'permission_callback' => '__return_true',
                                'callback'            => array( $this, 'get_status' ),
                        )
                );
        }

       public function get_status(): WP_REST_Response|WP_Error {
                global $wp_version;
                return $this->ok(
                        array(
                                'wordpress' => $wp_version,
                                'php'       => PHP_VERSION,
                                'plugin'    => ARTPULSE_VERSION,
                        )
                );
        }
}
