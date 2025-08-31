<?php
namespace ArtPulse\Admin;

/**
 * Handles post-event surveys and responses.
 */
class SurveyManager {

	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/survey' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/event/(?P<id>\\d+)/survey',
				array(
					'methods'             => array( 'GET', 'POST' ),
					'callback'            => array( self::class, 'handle' ),
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

	public static function handle( \WP_REST_Request $request ) {
		$event_id = absint( $request->get_param( 'id' ) );
		if ( ! $event_id ) {
			return new \WP_Error( 'invalid_event', 'Invalid event.', array( 'status' => 400 ) );
		}

		if ( $request->get_method() === 'GET' ) {
			$responses = get_post_meta( $event_id, 'ap_survey_responses', true );
			return \rest_ensure_response( is_array( $responses ) ? $responses : array() );
		}

		$answers = (array) $request->get_param( 'answers' );
		if ( empty( $answers ) ) {
			return new \WP_Error( 'invalid_data', 'No answers provided.', array( 'status' => 400 ) );
		}

		$responses = get_post_meta( $event_id, 'ap_survey_responses', true );
		if ( ! is_array( $responses ) ) {
			$responses = array();
		}
		$responses[] = array(
			'user_id' => get_current_user_id(),
			'answers' => $answers,
		);
		update_post_meta( $event_id, 'ap_survey_responses', $responses );

		do_action( 'artpulse_survey_submitted', get_current_user_id(), $event_id, $answers );

		return \rest_ensure_response( array( 'submitted' => true ) );
	}
}
