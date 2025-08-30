<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class RsvpBulkController {
    /**
     * Hook registration into rest_api_init.
     */
    public static function register(): void {
        $controller = new self();
        add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
    }

    /**
     * Register the bulk RSVP routes.
     */
    public function register_routes(): void {
        foreach ( array( ARTPULSE_API_NAMESPACE, 'ap/v1' ) as $namespace ) {
            register_rest_route(
                $namespace,
                '/rsvp/bulk',
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'bulk_update' ),
                    'permission_callback' => function ( WP_REST_Request $request ) {
                        $ok = \ArtPulse\Rest\Util\Auth::guard( $request->get_header( 'X-WP-Nonce' ), 'edit_posts' );
                        return $ok === true ? true : $ok;
                    },
                    'args'                => array(
                        'event_id' => array(
                            'type'     => 'integer',
                            'required' => true,
                        ),
                        'ids'      => array(
                            'type'     => 'array',
                            'required' => true,
                        ),
                        'status'   => array(
                            'type'     => 'string',
                            'required' => true,
                        ),
                    ),
                )
            );
        }
    }

    /**
     * Dummy bulk update handler.
     */
    public function bulk_update( WP_REST_Request $request ): WP_REST_Response {
        $ids = (array) $request->get_param( 'ids' );
        return rest_ensure_response( array( 'updated' => count( $ids ) ) );
    }
}
