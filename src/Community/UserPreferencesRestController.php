<?php
namespace ArtPulse\Community;

use ArtPulse\Personalization\RecommendationPreferenceManager;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class UserPreferencesRestController {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_meta' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_meta(): void {
		register_meta(
			'user',
			'ap_push_token',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_meta(
			'user',
			'ap_phone_number',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_meta(
			'user',
			'ap_sms_opt_in',
			array(
				'type'              => 'boolean',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
			)
		);

		register_meta(
			'user',
			'ap_notification_prefs',
			array(
				'type'              => 'object',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => array( self::class, 'sanitize_prefs' ),
			)
		);

		register_meta(
			'user',
			'ap_dashboard_theme',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_meta(
			'user',
			'ap_digest_frequency',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_meta(
			'user',
			'ap_digest_topics',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/user-preferences' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/user-preferences',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'save_preferences' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'push_token'            => array(
							'type'     => 'string',
							'required' => false,
						),
						'phone_number'          => array(
							'type'     => 'string',
							'required' => false,
						),
						'sms_opt_in'            => array(
							'type'     => 'boolean',
							'required' => false,
						),
						'dashboard_theme'       => array(
							'type'     => 'string',
							'required' => false,
						),
						'notification_prefs'    => array(
							'type'     => 'object',
							'required' => false,
						),
						'digest_frequency'      => array(
							'type'     => 'string',
							'required' => false,
						),
						'digest_topics'         => array(
							'type'     => 'string',
							'required' => false,
						),
						'ap_notification_nonce' => array(
							'type'     => 'string',
							'required' => true,
						),
						'preferred_tags'        => array(
							'type'     => 'array',
							'required' => false,
						),
						'ignored_tags'          => array(
							'type'     => 'array',
							'required' => false,
						),
						'blacklist_ids'         => array(
							'type'     => 'array',
							'required' => false,
						),
					),
				)
			);
		}
	}

	public static function save_preferences( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$nonce = $request->get_param( 'ap_notification_nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ap_notification_prefs' ) ) {
			return new WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'artpulse' ), array( 'status' => 403 ) );
		}

		$user_id = get_current_user_id();

		if ( $request->has_param( 'push_token' ) ) {
			update_user_meta( $user_id, 'ap_push_token', sanitize_text_field( $request['push_token'] ) );
		}

		if ( $request->has_param( 'phone_number' ) ) {
			update_user_meta( $user_id, 'ap_phone_number', sanitize_text_field( $request['phone_number'] ) );
		}

		if ( $request->has_param( 'sms_opt_in' ) ) {
			update_user_meta( $user_id, 'ap_sms_opt_in', $request['sms_opt_in'] ? 1 : 0 );
		}

		if ( $request->has_param( 'dashboard_theme' ) ) {
			update_user_meta( $user_id, 'ap_dashboard_theme', sanitize_text_field( $request['dashboard_theme'] ) );
		}

		if ( $request->has_param( 'notification_prefs' ) ) {
			$prefs = self::sanitize_prefs( $request['notification_prefs'] );
			update_user_meta( $user_id, 'ap_notification_prefs', $prefs );
		}

		if ( $request->has_param( 'digest_frequency' ) ) {
			update_user_meta( $user_id, 'ap_digest_frequency', sanitize_text_field( $request['digest_frequency'] ) );
		}

		if ( $request->has_param( 'digest_topics' ) ) {
			update_user_meta( $user_id, 'ap_digest_topics', sanitize_text_field( $request['digest_topics'] ) );
		}

		$pref_update = array();
		if ( $request->has_param( 'preferred_tags' ) ) {
			$pref_update['preferred_tags'] = array_map( 'sanitize_text_field', (array) $request['preferred_tags'] );
		}
		if ( $request->has_param( 'ignored_tags' ) ) {
			$pref_update['ignored_tags'] = array_map( 'sanitize_text_field', (array) $request['ignored_tags'] );
		}
		if ( $request->has_param( 'blacklist_ids' ) ) {
			$pref_update['blacklist_ids'] = array_map( 'intval', (array) $request['blacklist_ids'] );
		}
		if ( $pref_update ) {
			RecommendationPreferenceManager::update( $user_id, $pref_update );
		}

		return \rest_ensure_response( array( 'status' => 'saved' ) );
	}

	public static function sanitize_prefs( $value ): array {
		$raw   = (array) $value;
		$prefs = array(
			'email' => ! empty( $raw['email'] ),
			'push'  => ! empty( $raw['push'] ),
			'sms'   => ! empty( $raw['sms'] ),
		);
		return $prefs;
	}
}
