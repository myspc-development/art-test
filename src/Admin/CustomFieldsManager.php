<?php
namespace ArtPulse\Admin;

/**
 * Manages custom RSVP fields.
 */
class CustomFieldsManager {

	/**
	 * Register hooks.
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * REST API routes for retrieving and saving custom field data.
	 */
	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/rsvp/custom-fields' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/rsvp/custom-fields',
				array(
					'methods'             => array( 'GET', 'POST' ),
					'callback'            => array( self::class, 'route_handler' ),
					'permission_callback' => array( self::class, 'check_permission' ),
					'args'                => array( 'id' => array( 'validate_callback' => 'absint' ) ),
				)
			);
		}
	}

	public static function check_permission() {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function route_handler( \WP_REST_Request $request ) {
		$event_id = absint( $request->get_param( 'id' ) );
		if ( ! $event_id ) {
			return new \WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		if ( $request->get_method() === 'GET' ) {
			$fields = get_post_meta( $event_id, 'ap_rsvp_custom_fields', true );
			return \rest_ensure_response( is_array( $fields ) ? $fields : array() );
		}

		$fields    = (array) $request->get_param( 'fields' );
		$sanitized = array();
		foreach ( $fields as $key => $label ) {
			$sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $label );
		}
		update_post_meta( $event_id, 'ap_rsvp_custom_fields', $sanitized );

		return \rest_ensure_response( $sanitized );
	}
}
