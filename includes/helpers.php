<?php
declare(strict_types=1);

/**
 * Write a message to the PHP error log when debugging is enabled.
 *
 * Ensures the message is sanitized and avoids leaking sensitive data in
 * production environments where WP_DEBUG is disabled.
 */
function ap_log( $message ): void {
	$debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
	if ( isset( $GLOBALS['ap_debug_override'] ) ) {
		$debug = (bool) $GLOBALS['ap_debug_override'];
	}
	if ( ! $debug ) {
		return;
	}
	if ( is_array( $message ) || is_object( $message ) ) {
		$message = wp_json_encode( $message );
	}
	$message = sanitize_text_field( (string) $message );
	error_log( $message );
}

/**
 * Escape a term for use within a SQL LIKE clause.
 */
function ap_db_like( string $term ): string {
	global $wpdb;
	return '%' . $wpdb->esc_like( $term ) . '%';
}

function ap_get_ui_mode(): string {
	if ( isset( $_GET['ui_mode'] ) ) {
		return sanitize_text_field( $_GET['ui_mode'] );
	}
	return get_option( 'ap_ui_mode', 'salient' );
}

function ap_get_portfolio_display_mode(): string {
	return get_option( 'ap_portfolio_display', 'plugin' );
}

/**
 * Simple object cache wrapper for expensive queries.
 */
function ap_cache_get( string $key, callable $callback, int $expires = HOUR_IN_SECONDS ) {
	$group = 'artpulse_queries';
	$value = wp_cache_get( $key, $group );
	if ( false === $value ) {
		$value = $callback();
		wp_cache_set( $key, $value, $group, $expires );
	}
	return $value;
}

/**
 * Fetch and parse an RSS feed.
 *
 * Wraps WordPress fetch_feed() with basic error handling.
 *
 * @param string $url Feed URL.
 * @return array|SimplePie
 */
function ap_get_feed( string $url ): array|SimplePie {
	include_once ABSPATH . WPINC . '/feed.php';

	$feed = fetch_feed( $url );
	if ( is_wp_error( $feed ) ) {
		return array();
	}

	return $feed;
}

function ap_template_context( array $args = array(), array $defaults = array() ): array {
	return wp_parse_args( $args, $defaults );
}

function ap_safe_include( string $relative_template, string $fallback_path, array $context = array() ): void {
	$template = locate_template( $relative_template );
	if ( ! $template ) {
		$template = $fallback_path;
	}
	if ( $template && file_exists( $template ) ) {
		if ( ! empty( $context ) ) {
			extract( $context, EXTR_SKIP );
		}
		include $template;
	} else {
		ap_log( "ArtPulse: Missing template → $relative_template or fallback." );
	}
}

/**
 * Locate a template allowing theme overrides similar to WooCommerce.
 *
 * @param string $relative_template Relative path within the theme.
 * @param string $plugin_path       Default path in the plugin.
 * @return string Absolute file path to load.
 */
function ap_locate_template( string $relative_template, string $plugin_path ): string {
	$template = locate_template( $relative_template );
	if ( ! $template ) {
		$template = trailingslashit( get_stylesheet_directory() ) . $relative_template;
		if ( ! file_exists( $template ) ) {
			$template = $plugin_path;
		}
	}
	/**
	 * Filter located template path.
	 *
	 * @param string $template Located template file path.
	 * @param string $relative_template Requested relative template.
	 */
	return apply_filters( 'ap_locate_template', $template, $relative_template );
}

function ap_clear_portfolio_cache(): void {
	wp_cache_flush();
}

/**
 * Determine if the roles dashboard v2 is enabled.
 *
 * Supports URL override (?ap_v2=0|1) stored in session.
 */
function ap_dashboard_v2_enabled(): bool {
	if ( PHP_SAPI !== 'cli' && ! headers_sent() && ! session_id() ) {
		session_start();
	}

	if ( isset( $_GET['ap_v2'] ) ) {
		$_SESSION['ap_v2'] = $_GET['ap_v2'] === '1' ? 1 : 0;
		return $_SESSION['ap_v2'] === 1;
	}

	if ( isset( $_SESSION['ap_v2'] ) ) {
		return $_SESSION['ap_v2'] === 1;
	}

	$opts = get_option( 'artpulse_settings', array() );
	if ( ! array_key_exists( 'dashboard_v2', $opts ) ) {
		return true;
	}

	return (bool) $opts['dashboard_v2'];
}

/**
 * Render the unified dashboard template for the current user.
 *
 * @param string[] $allowed_roles Roles permitted to view the dashboard.
 */
function ap_render_dashboard( array $allowed_roles = array() ): void {
	if ( ! defined( 'AP_DASHBOARD_RENDERING' ) ) {
		define( 'AP_DASHBOARD_RENDERING', true );
	}

	$allowed_roles = array_map( 'sanitize_key', $allowed_roles );
	$user_role     = function_exists( 'ap_get_effective_role' ) ? ap_get_effective_role() : \ArtPulse\Core\DashboardController::get_role( get_current_user_id() );

        if (
                ! headers_sent() &&
                defined( 'AP_VERBOSE_DEBUG' ) &&
                AP_VERBOSE_DEBUG &&
                function_exists( 'is_user_logged_in' ) &&
                is_user_logged_in()
        ) {
                header( 'X-AP-Resolved-Role: ' . $user_role );
        }

	if ( $allowed_roles && ! in_array( $user_role, $allowed_roles, true ) ) {
		wp_die( __( 'Access denied', 'artpulse' ) );
	}

	ap_safe_include(
		'dashboard-role.php',
		plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/dashboard-role.php',
		array(
			'allowed_roles' => $allowed_roles,
			'user_role'     => $user_role,
		)
	);
}


/**
 * Render a static role layout shell listing preset widget slots.
 *
 * @param string $role Role slug.
 */
function ap_render_role_layout_template( string $role ): void {
        $role  = sanitize_key( $role );
        $slugs = \ArtPulse\Admin\RolePresets::get_preset_slugs( $role );
        echo '<section class="ap-role-layout" role="tabpanel" id="ap-panel-' . esc_attr( $role ) . '" aria-labelledby="ap-tab-' . esc_attr( $role ) . '" data-role="' . esc_attr( $role ) . '">';
        foreach ( $slugs as $slug ) {
                echo '<section data-slug="' . esc_attr( $slug ) . '"></section>';
        }
        echo '</section>';
}
