<?php
namespace ArtPulse\AI;

use WP_REST_Request;

class GrantAssistant {

	public static function register(): void {
		add_action( 'init', array( self::class, 'maybe_install_table' ) );
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/ai/generate-grant-copy' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/ai/generate-grant-copy',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'generate' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'type'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'tone'   => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'source' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'save'   => array(
							'type'              => 'boolean',
							'sanitize_callback' => 'rest_sanitize_boolean',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'org_id' => array(
							'type'              => 'integer',
							'sanitize_callback' => 'absint',
							'validate_callback' => 'is_numeric',
						),
					),
				)
			);
		}

		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/ai/rewrite' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/ai/rewrite',
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'rewrite' ),
					'permission_callback' => fn() => is_user_logged_in(),
					'args'                => array(
						'text' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_textarea_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'tone' => array(
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_key',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				)
			);
		}
	}

	public static function maybe_install_table(): void {
		global $wpdb;
		$table  = $wpdb->prefix . 'ap_generated_grant_copy';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $exists !== $table ) {
			$charset = $wpdb->get_charset_collate();
			$sql     = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                org_id INT NULL,
                user_id BIGINT NULL,
                type VARCHAR(40) NULL,
                tone VARCHAR(20) NULL,
                source TEXT NULL,
                draft TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY org_id (org_id),
                KEY user_id (user_id)
            ) $charset;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	private static function get_prompt( string $type, string $tone ): string {
		$templates = array(
			'project_summary'   => 'Write a 150 word project summary.',
			'public_benefit'    => 'Describe the public benefit.',
			'mission_alignment' => 'Explain how this aligns with the mission.',
		);

		$tones = array(
			'formal'     => 'Use a formal grant application tone.',
			'public'     => 'Use an engaging public voice.',
			'curatorial' => 'Write for a curatorial audience.',
			'grant'      => 'Write for a grant application.',
		);

		return ( $templates[ $type ] ?? $templates['project_summary'] ) . ' ' . ( $tones[ $tone ] ?? '' );
	}

	public static function generate( WP_REST_Request $req ) {
		$type   = sanitize_key( $req->get_param( 'type' ) );
		$tone   = sanitize_key( $req->get_param( 'tone' ) );
		$source = sanitize_textarea_field( $req->get_param( 'source' ) );

		$draft  = trim( self::get_prompt( $type, $tone ) . ' ' . $source );
		$output = wpautop( esc_html( $draft ) );

		if ( $req->get_param( 'save' ) ) {
			global $wpdb;
			$wpdb->insert(
				$wpdb->prefix . 'ap_generated_grant_copy',
				array(
					'org_id'     => absint( $req->get_param( 'org_id' ) ),
					'user_id'    => get_current_user_id(),
					'type'       => $type,
					'tone'       => $tone,
					'source'     => $source,
					'draft'      => $draft,
					'created_at' => current_time( 'mysql' ),
				)
			);
		}

		return \rest_ensure_response(
			array(
				'draft'  => $draft,
				'output' => $output,
			)
		);
	}

	public static function rewrite( WP_REST_Request $req ) {
		$text = sanitize_textarea_field( $req->get_param( 'text' ) );
		$tone = sanitize_key( $req->get_param( 'tone' ) );

		$prompt = self::get_prompt( 'rewrite', $tone );
		$draft  = trim( ( $prompt ?: 'Rewrite:' ) . ' ' . $text );
		$output = wpautop( esc_html( $draft ) );

		if ( $req->get_param( 'save' ) ) {
			global $wpdb;
			$wpdb->insert(
				$wpdb->prefix . 'ap_generated_grant_copy',
				array(
					'org_id'     => absint( $req->get_param( 'org_id' ) ),
					'user_id'    => get_current_user_id(),
					'type'       => 'rewrite',
					'tone'       => $tone,
					'source'     => $text,
					'draft'      => $draft,
					'created_at' => current_time( 'mysql' ),
				)
			);
		}

		return \rest_ensure_response(
			array(
				'draft'  => $draft,
				'output' => $output,
			)
		);
	}
}
