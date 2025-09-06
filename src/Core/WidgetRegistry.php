<?php
namespace ArtPulse\Core;

/**
 * Central registry for dashboard widgets.
 */
class WidgetRegistry {

	/**
	 * Registered widgets mapped by slug.
	 *
	 * @var array<string, array{render: callable, args: array}>
	 */
	private static array $widgets = array();

	/**
	 * Track missing slugs already logged.
	 *
	 * @var array<string, bool>
	 */
	private static array $logged_missing = array();

	/**
	 * Override debug mode for missing widget rendering.
	 */
	private static ?bool $debugOverride = null;

	/**
	 * Fire registration hook on init so other modules can register widgets.
	 */
	public static function init(): void {
		do_action( 'artpulse/widgets/register', self::class );
	}

	/**
	 * Register a widget render callback.
	 */
	public static function register( string $slug, callable $render, array $args = array() ): void {
		$key = self::normalize_slug( $slug );
		if ( $key === '' ) {
			return;
		}
		self::$widgets[ $key ] = array(
			'render' => $render,
			'args'   => $args,
		);
	}

	/**
	 * Determine if a widget slug exists.
	 */
	public static function exists( string $slug ): bool {
		$key = self::normalize_slug( $slug );
		return isset( self::$widgets[ $key ] );
	}

	/**
	 * Render a widget by slug.
	 */
	public static function render( string $slug, array $context = array() ): string {
		$key = self::normalize_slug( $slug );
		if ( ! isset( self::$widgets[ $key ] ) ) {
			if ( ! isset( self::$logged_missing[ $key ] ) ) {
				self::$logged_missing[ $key ] = true;
				if ( self::should_debug() ) {
					error_log( 'ArtPulse: Unknown widget slug: ' . $key );
				}
			}
			if ( self::should_debug() ) {
				$escaped = function_exists( 'esc_attr' ) ? esc_attr( $key ) : htmlspecialchars( $key, ENT_QUOTES );
				return '<section class="ap-widget--missing" data-slug="' . $escaped . '"></section>';
			}
			return '';
		}
		$def  = self::$widgets[ $key ];
		$args = array_merge( $def['args'], $context );
		return (string) call_user_func( $def['render'], $args );
	}

	/**
	 * List all registered widgets.
	 *
	 * @return array<string>
	 */
	public static function list(): array {
		return array_keys( self::$widgets );
	}

	/**
	 * Retrieve all registered widget IDs.
	 *
	 * @return array<string>
	 */
	public static function ids(): array {
		return array_keys( self::$widgets ?? array() );
	}

	/** Return all canonical widget IDs */
	public static function get_canonical_ids(): array {
		return array_keys( self::$widgets );
	}

	/**
	 * Override debug mode for missing widget placeholder rendering.
	 */
	public static function setDebug( ?bool $debug ): void {
		self::$debugOverride = $debug;
	}

	/**
	 * Clear debug override so WP_DEBUG is used instead.
	 */
	public static function resetDebug(): void {
		self::$debugOverride = null;
	}

		/** Normalize a slug to its canonical form */
	public static function normalize_slug( string $slug ): string {
		if ( class_exists( DashboardWidgetRegistry::class ) ) {
				return DashboardWidgetRegistry::canon_slug( $slug );
		}
			return \ArtPulse\Support\WidgetIds::canonicalize( $slug );
	}

	private static function should_debug(): bool {
		if ( self::$debugOverride !== null ) {
			return self::$debugOverride;
		}
		return defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG
			&& function_exists( 'is_user_logged_in' ) && is_user_logged_in();
	}
}

if ( function_exists( 'add_action' ) ) {
	add_action( 'init', array( WidgetRegistry::class, 'init' ) );
}
