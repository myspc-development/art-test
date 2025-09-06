<?php
declare(strict_types=1);

namespace ArtPulse\Frontend;

class ShortcodeRoleDashboard {

	/** prevent duplicate enqueues */
	private static bool $did_enqueue = false;
	/** allowed roles */
	private const ALLOWED_ROLES = array( 'member', 'artist', 'organization', 'admin' );

	public static function register(): void {
		add_shortcode( 'ap_role_dashboard', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'maybe_enqueue' ) );
		// Allow previewing via ?ap_preview_role=role even if the page lacks the shortcode.
		add_filter( 'the_content', array( self::class, 'inject_container_if_preview' ), 9 );
	}

	/** ensure preview requests come from admins with a valid nonce */
	private static function can_preview(): bool {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$nonce = isset( $_GET['ap_preview_nonce'] ) ? sanitize_key( (string) $_GET['ap_preview_nonce'] ) : '';
		return isset( $_GET['ap_preview_role'] ) && wp_verify_nonce( $nonce, 'ap_preview' );
	}

	/** derive role from shortcode/user and allow ?ap_preview_role override for logged-in users */
	private static function role_from_request( ?string $fallback ): string {
		$role = $fallback && in_array( $fallback, self::ALLOWED_ROLES, true ) ? $fallback : 'member';
		if ( self::can_preview() ) {
			$preview = sanitize_key( (string) $_GET['ap_preview_role'] );
			if ( in_array( $preview, self::ALLOWED_ROLES, true ) ) {
				$role = $preview;
			}
		}
		return $role;
	}

	/** parse first [ap_role_dashboard] role from content */
	private static function extract_role_from_content( string $content ): ?string {
		if ( $content === '' ) {
			return null;
		}
		$pattern = get_shortcode_regex( array( 'ap_role_dashboard' ) );
		if ( ! preg_match_all( '/' . $pattern . '/s', $content, $matches, PREG_SET_ORDER ) ) {
			return null;
		}
		foreach ( $matches as $sc ) {
			if ( $sc[2] !== 'ap_role_dashboard' ) {
				continue;
			}
			$atts = shortcode_parse_atts( $sc[3] ?? '' ) ?: array();
			$role = isset( $atts['role'] ) ? sanitize_key( (string) $atts['role'] ) : 'member';
			return in_array( $role, self::ALLOWED_ROLES, true ) ? $role : 'member';
		}
		return null;
	}

	/** localize safe script data (no user_login) */
	private static function script_data( string $role ): array {
		$u = wp_get_current_user();
		return array(
			'user'            => array(
				'id'          => (int) $u->ID,
				'displayName' => sanitize_text_field( (string) $u->display_name ),
			),
			'role'            => $role,
			'restBase'        => esc_url_raw( rest_url( \defined( 'ARTPULSE_API_NAMESPACE' ) ? \ARTPULSE_API_NAMESPACE : 'artpulse/v1' ) ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'seenDashboardV2' => (bool) get_user_meta( $u->ID, 'ap_seen_dashboard_v2', true ),
		);
	}

	public static function enqueue_assets( string $role ): void {
		if ( self::$did_enqueue ) {
			return;
		}
		self::$did_enqueue = true;

		// Dequeue legacy user dashboard if present to prevent UI conflicts
		foreach ( array( 'ap-user-dashboard', 'ap-user-dashboard-css', 'ap-user-dashboard-styles' ) as $old ) {
			if ( wp_script_is( $old, 'enqueued' ) || wp_script_is( $old, 'registered' ) ) {
				wp_dequeue_script( $old );
				wp_deregister_script( $old );
			}
			if ( wp_style_is( $old, 'enqueued' ) || wp_style_is( $old, 'registered' ) ) {
				wp_dequeue_style( $old );
				wp_deregister_style( $old );
			}
		}

				// New role dashboard bundle
				$script_handle = 'ap-role-dashboard';
				$script_rel    = 'assets/dist/RoleDashboard.js';
				$script_path   = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $script_rel;
		if ( file_exists( $script_path ) ) {
				wp_enqueue_script(
					$script_handle,
					plugins_url( $script_rel, ARTPULSE_PLUGIN_FILE ),
					array( 'wp-element' ),
					(string) filemtime( $script_path ),
					true
				);
				wp_script_add_data( $script_handle, 'type', 'module' );
				wp_localize_script( $script_handle, 'apDashboardData', self::script_data( $role ) );
		}

		$style_handle = 'ap-role-dashboard-css';
		$style_path   = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/css/dashboard.css';
		if ( file_exists( $style_path ) ) {
			wp_enqueue_style(
				$style_handle,
				plugins_url( 'assets/css/dashboard.css', ARTPULSE_PLUGIN_FILE ),
				array(),
				(string) filemtime( $style_path )
			);
		}
	}

	/** enqueue when shortcode is present OR when preview role is set */
	public static function maybe_enqueue(): void {
		if ( ! is_user_logged_in() || ! is_singular() ) {
			return;
		}

		$post_id    = get_queried_object_id();
		$content    = (string) get_post_field( 'post_content', $post_id );
		$has_sc     = has_shortcode( $content, 'ap_role_dashboard' );
		$is_preview = self::can_preview();
		if ( ! $has_sc && ! $is_preview ) {
			return;
		}

		$role = self::role_from_request(
			$has_sc ? ( self::extract_role_from_content( $content ) ?? 'member' ) : 'member'
		);
		self::enqueue_assets( $role );
	}

	/** inject container on preview pages lacking the shortcode */
	public static function inject_container_if_preview( string $content ): string {
		if ( ! is_user_logged_in() || ! is_singular() ) {
			return $content;
		}
		if ( ! self::can_preview() ) {
			return $content;
		}
		if ( has_shortcode( $content, 'ap_role_dashboard' ) || strpos( $content, 'id="ap-dashboard-root"' ) !== false ) {
			return $content;
		}
		$role      = self::role_from_request( 'member' );
		$container = sprintf(
			'<div id="ap-dashboard-root" class="ap-dashboard-grid" data-role="%s"></div>',
			esc_attr( $role )
		);
		return $content . "\n\n" . $container;
	}

	public static function render( array $atts = array() ): string {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$atts = shortcode_atts( array( 'role' => 'member' ), $atts, 'ap_role_dashboard' );
		$role = sanitize_key( (string) $atts['role'] );
		if ( ! in_array( $role, self::ALLOWED_ROLES, true ) ) {
			$role = 'member';
		}
		$role = self::role_from_request( $role );

		self::enqueue_assets( $role );
		return sprintf(
			'<div id="ap-dashboard-root" class="ap-dashboard-grid" data-role="%s"></div>',
			esc_attr( $role )
		);
	}
}
