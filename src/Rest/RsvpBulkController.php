<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;
use ArtPulse\Rest\RestResponder;

class RsvpBulkController {
	use RestResponder;

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
					'permission_callback' => Auth::require_login_and_cap( 'edit_posts' ),
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
	public function bulk_update( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$ids = (array) $request->get_param( 'ids' );
		return \rest_ensure_response( array( 'updated' => count( $ids ) ) );
	}
}
