<?php

namespace ArtPulse\Core;

use ArtPulse\DashboardWidgetRegistryLoader;
use ArtPulse\Dashboard\WidgetVisibility;
use WP_Roles;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\RolePresets;
use ArtPulse\Core\WidgetFlags;
use ArtPulse\Support\WidgetIds;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

/**
 * Simple registry for dashboard widgets.
 */
class DashboardWidgetRegistry {
	/**
	 * @var array<string,array{
	 *     label:string,
	 *     icon:string,
	 *     description:string,
	 *     callback:callable,
	 *     category?:string,
	 *     roles?:array,
	 *     settings?:array,
	 *     tags?:array,
	 *     capability?:string,
	 *     cache?:bool,
	 *     lazy?:bool,
	 *     visibility?:string,
	 *     builder_only?:bool
	 * }>
	 */
	private static array $widgets = array();

	/**
	 * Builder widget definitions used by the dashboard builder UI.
	 *
	 * @var array<string,array>
	 */
	private static array $builder_widgets = array();

	/**
	 * Cached mapping of builder IDs to core IDs.
	 *
	 * @var array<string,string>|null
	 */
	private static ?array $id_map = null;

	/**
	 * Issues collected during registration for audit purposes.
	 *
	 * @var array<string>
	 */
	private static array $issues = array();

	/**
	 * Track duplicate registration notices to avoid log spam.
	 *
	 * @var array<string,bool>
	 */
	private static array $logged_duplicates = array();

	/**
	 * Map of widget ID aliases to their canonical IDs.
	 *
	 * @var array<string,string>
	 */
	private static array $aliases = array();

	/**
	 * Ensure callbacks consistently accept a user ID parameter.
	 *
	 * If the provided callback does not require a parameter or its first
	 * parameter is not an integer, wrap it in a closure that ignores the user
	 * ID. This allows legacy callbacks to remain compatible while the
	 * registry always invokes callbacks with the user ID argument.
	 */
	private static function normalize_callback( callable $callback ): callable {
		try {
			$ref = is_array( $callback )
				? new \ReflectionMethod( $callback[0], $callback[1] )
				: new \ReflectionFunction( $callback );

			$params = $ref->getParameters();
			if ( ! $params ) {
				return static function ( int $user_id = 0 ) use ( $callback ) {
					return call_user_func( $callback );
				};
			}

			$first = $params[0];
			$type  = $first->getType();
			if ( $type instanceof \ReflectionNamedType && $type->getName() !== 'int' ) {
				return static function ( int $user_id = 0 ) use ( $callback ) {
					return call_user_func( $callback );
				};
			}
		} catch ( \ReflectionException $e ) {
			// Ignore reflection errors and fall back to original callback.
		}

		return $callback;
	}

	/**
	 * Normalize a role list into a de-duplicated, lower-cased array.
	 *
	 * @param mixed $roles Role list in various formats.
	 */
	private static function normalizeRoleList( $roles ): array {
		if ( is_string( $roles ) ) {
			$s = trim( $roles );
			if ( $s !== '' && ( $s[0] === '[' || $s[0] === '{' ) ) {
				$decoded = json_decode( $s, true );
				$roles   = is_array( $decoded ) ? $decoded : array_map( 'trim', explode( ',', $s ) );
			} else {
				$roles = array_map( 'trim', explode( ',', $s ) );
			}
		} elseif ( $roles instanceof \Traversable ) {
			$roles = iterator_to_array( $roles );
		}
		if ( ! is_array( $roles ) ) {
			$roles = array();
		}
		$roles = array_values(
			array_unique(
				array_filter(
					array_map(
						static fn( $r ) => strtolower( trim( (string) $r ) ),
						$roles
					)
				)
			)
		);
		return $roles;
	}

	/**
	 * Canonicalize a widget slug by lower-casing, sanitizing and ensuring the
	 * `widget_` prefix is present.
	 */
	public static function canon_slug( string $slug ): string {
		$s = WidgetIds::canonicalize( $slug );
		return self::$aliases[ $s ] ?? $s;
	}

	/**
	 * Register an alternate widget ID that maps to a canonical ID.
	 */
	public static function alias( string $alias, string $canonical ): void {
		$a = WidgetIds::canonicalize( $alias );
		$c = WidgetIds::canonicalize( $canonical );
		if ( $a === '' || $c === '' || $a === $c ) {
			return;
		}
		self::$aliases[ $a ] = $c;
		if ( isset( self::$widgets[ $a ] ) ) {
			if ( ! isset( self::$widgets[ $c ] ) ) {
				self::$widgets[ $c ] = self::$widgets[ $a ];
			}
			unset( self::$widgets[ $a ] );
		}
	}

	/**
	 * Update a widget's render callback.
	 */
	public static function bindRenderer( string $id, callable $callback ): void {
		$cid = self::canon_slug( $id );
		if ( ! $cid || ! isset( self::$widgets[ $cid ] ) ) {
			return;
		}
		self::$widgets[ $cid ]['callback'] = self::normalize_callback( $callback );
		if ( is_array( $callback ) && isset( $callback[0] ) && is_string( $callback[0] ) ) {
			self::$widgets[ $cid ]['class'] = $callback[0];
		}
	}

	/**
	 * Attempt to derive a callback based on widget id naming conventions.
	 */
	private static function derive_callback( string $id ): ?callable {
		$base  = strpos( $id, 'widget_' ) === 0 ? substr( $id, 7 ) : $id;
		$class = 'ArtPulse\\Widgets\\' . str_replace( ' ', '', ucwords( str_replace( '_', ' ', $base ) ) ) . 'Widget';
		if ( class_exists( $class ) && method_exists( $class, 'render' ) ) {
			return array( $class, 'render' );
		}
		return null;
	}

	/**
	 * Retrieve a widget definition by slug.
	 */
	public static function getById( string $slug ): ?array {
		$id = self::canon_slug( $slug );
		if ( $id === '' ) {
			return null;
		}

		return self::$widgets[ $id ] ?? null;
	}

	/**
	 * Determine if a widget ID exists in the registry.
	 */
	public static function exists( string $slug ): bool {
		$id = self::canon_slug( $slug );

		return $id !== '' && isset( self::$widgets[ $id ] );
	}

	/**
	 * Backwards compatibility helper.
	 *
	 * @param string $slug Widget identifier.
	 */
	public static function has( string $slug ): bool {
		return self::exists( $slug );
	}

	/**
	 * Snapshot of the registry for debugging.
	 *
	 * @return array{registered_ids:array<int,string>,count:int}
	 */
	public static function debug_snapshot(): array {
		return array(
			'registered_ids' => array_keys( self::$widgets ),
			'count'          => count( self::$widgets ),
		);
	}

	/**
	 * Return all registered widget definitions (core registry, not builder).
	 *
	 * @param string|null $visibility Optional visibility filter.
	 * @param bool        $builder    When true, returns builder widgets instead.
	 */
	public static function all(): array {
		return self::$widgets;
	}

	/**
	 * Replace the internal widget registry.
	 *
	 * Primarily intended for tests and WP-CLI utilities.
	 *
	 * @param array<string,array> $widgets Widget definitions.
	 */
	public static function set( array $widgets ): void {
		self::$widgets        = $widgets;
		self::$builder_widgets = array();
		self::$id_map         = null;
		self::$issues         = array();
		self::$aliases        = array();
	}

	/**
	 * Alias of set().
	 *
	 * Primarily intended for tests and WP-CLI utilities.
	 *
	 * @param array<string,array> $widgets Widget definitions.
	 */
	public static function set_widgets( array $widgets ): void {
		self::set( $widgets );
	}

	/**
	 * Retrieve registration issues collected during runtime.
	 */
	public static function issues(): array {
		return self::$issues;
	}

	/**
	 * Register a widget and its settings.
	 */
	public static function register(
		string $id,
		string|array $label,
		string $icon = '',
		string $description = '',
		?callable $callback = null,
		array $options = array()
	): array {
		// Builder-style registration when the second argument is an array.
		if ( is_array( $label ) ) {
			$id = sanitize_key( $id );
			if ( ! $id ) {
			        return array();
			}
			$base = strpos( $id, 'widget_' ) === 0 ? substr( $id, 7 ) : $id;
			$args = array_merge(
				array(
					'title'           => '',
					'render_callback' => null,
					'roles'           => array(),
					'file'            => '',
					'visibility'      => WidgetVisibility::PUBLIC,
				),
				$label
			);

			if ( ! is_callable( $args['render_callback'] ) ) {
				$args['render_callback'] = static function () {};
			}
			$visibility = in_array( $args['visibility'], WidgetVisibility::values(), true )
				? $args['visibility']
				: WidgetVisibility::PUBLIC;

			$args['roles'] = self::normalizeRoleList( $args['roles'] ?? array() );

			self::$builder_widgets[ $base ] = array(
			        'id'              => $base,
			        'title'           => (string) $args['title'],
			        'render_callback' => $args['render_callback'],
			        'roles'           => $args['roles'],
			        'file'            => (string) $args['file'],
			        'visibility'      => $visibility,
			);

			return self::$builder_widgets[ $base ];
		}

		// Core-style registration.
		$id = self::canon_slug( $id );
		if ( ! $id ) {
			return array();
		}
		$options['roles'] = self::normalizeRoleList( $options['roles'] ?? array() );

		if ( isset( self::$widgets[ $id ] ) ) {
			if ( empty( self::$logged_duplicates[ $id ] ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( "AP: widget id already registered: $id" );
				self::$logged_duplicates[ $id ] = true;
			}
			return self::$widgets[ $id ];
		}

		$label       = trim( $label );
		$description = trim( $description );
		if ( ! did_action( 'init' ) && ( str_contains( $label, '__(' ) || str_contains( $description, '__(' ) ) ) {
			_doing_it_wrong( __METHOD__, 'Translated strings not allowed during registration; pass plain strings.', '1.0.0' );
			$label       = preg_replace( '/__\(([^)]+)\)/', '$1', $label );
			$description = preg_replace( '/__\(([^)]+)\)/', '$1', $description );
		}

		if ( ! is_callable( $callback ) ) {
			$callback = self::derive_callback( $id );
		}

		$status = $options['status'] ?? 'active';
		if ( in_array( $status, array( 'coming_soon', 'beta' ), true ) && ( ! defined( 'AP_STRICT_FLAGS' ) || ! AP_STRICT_FLAGS ) ) {
			$status = 'active';
		}

		if ( ! is_callable( $callback ) ) {
			self::$issues[] = $id;
			$callback       = static function () {};
			$status         = 'inactive';
		}

		if ( ! WidgetFlags::is_active( $id ) ) {
			$status = 'inactive';
		}

		$callback = self::normalize_callback( $callback );

		$class = '';
		if ( is_array( $callback ) && isset( $callback[0] ) && is_string( $callback[0] ) ) {
			$class = $callback[0];
		}

		self::$widgets[ $id ] = array(
			'label'        => $label,
			'icon'         => $icon,
			'description'  => $description,
			'callback'     => $callback,
			'class'        => $class,
			'category'     => $options['category'] ?? '',
			'group'        => $options['group'] ?? '',
			'section'      => $options['section'] ?? '',
			'roles'        => $options['roles'],
			'settings'     => $options['settings'] ?? array(),
			'tags'         => $options['tags'] ?? array(),
			'capability'   => $options['capability'] ?? '',
			'cache'        => $options['cache'] ?? false,
			'lazy'         => $options['lazy'] ?? false,
			'visibility'   => $options['visibility'] ?? WidgetVisibility::PUBLIC,
			'builder_only' => $options['builder_only'] ?? false,
			'status'       => $status,
		);

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$roles = isset( self::$widgets[ $id ]['roles'] ) ? implode( ',', (array) self::$widgets[ $id ]['roles'] ) : 'all';
			\WP_CLI::debug( "widget register {$id} roles={$roles}", 'artpulse' );
		}

		return self::$widgets[ $id ];
	}

	/**
	 * Simplified widget registration used by generic dashboards.
	 * Supports all options from register(), including optional 'tags'.
	 *
	 * @param string $id   Widget identifier.
	 * @param array  $args Configuration arguments. Accepts `label`, `callback`,
	 *                     and optional `roles` to limit visibility by role.
	 */
	public static function register_widget( string $id, array $args ): array {
		$id = self::canon_slug( $id );
		if ( ! $id ) {
			return array();
		}

		$args['roles'] = self::normalizeRoleList( $args['roles'] ?? array() );

		if ( isset( self::$widgets[ $id ] ) ) {
			if ( $args['roles'] ) {
				$prior                         = self::$widgets[ $id ]['roles'] ?? array();
				self::$widgets[ $id ]['roles'] = array_values( array_unique( array_merge( $prior, $args['roles'] ) ) );
			}
			return self::$widgets[ $id ];
		}

		$label = trim( $args['label'] ?? 'Untitled' );

		$args['label']   = $label;
		$args['group']   = $args['group'] ?? '';
		$args['section'] = $args['section'] ?? '';

		if ( empty( $args['callback'] ) && isset( $args['template'] ) ) {
			$template = $args['template'];
			$path     = locate_template( $template );
			if ( ! $path ) {
				$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $template;
			}
			if ( ! file_exists( $path ) ) {
				do_action( 'ap_widget_missing_template', $id, $template );
				return array();
			}
			$args['callback'] = static function () use ( $path ) {
				ob_start();
				include $path;
				return ob_get_clean();
			};
		}

		if ( empty( $args['callback'] ) ) {
			$args['callback'] = self::derive_callback( $id );
		}

		$status = $args['status'] ?? 'active';
		if ( in_array( $status, array( 'coming_soon', 'beta' ), true ) && ( ! defined( 'AP_STRICT_FLAGS' ) || ! AP_STRICT_FLAGS ) ) {
			$status = 'active';
		}

		if ( empty( $args['callback'] ) || ! is_callable( $args['callback'] ) ) {
			self::$issues[]   = $id;
			$args['callback'] = static function () {};
			$status           = 'inactive';
		} else {
			$args['callback'] = self::normalize_callback( $args['callback'] );
		}

		if ( ! WidgetFlags::is_active( $id ) ) {
			$status = 'inactive';
		}

		$class = '';
		if ( is_array( $args['callback'] ) && isset( $args['callback'][0] ) && is_string( $args['callback'][0] ) ) {
			$class = $args['callback'][0];
		}

		$args['id']           = $id;
		$args['class']        = $class;
		$args['status']       = $status;
		self::$widgets[ $id ] = $args;

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$roles = isset( $args['roles'] ) ? implode( ',', (array) $args['roles'] ) : 'all';
			\WP_CLI::debug( "widget register {$id} roles={$roles}", 'artpulse' );
		}

		return self::$widgets[ $id ];
	}

	/**
	 * Convenience wrapper for registering a widget visible only to given roles.
	 */
	public static function register_widget_for_roles( string $id, array $args, array $roles ): void {
		$args['roles'] = $roles;
		self::register_widget( $id, $args );
	}

	/**
	 * Update an existing widget definition.
	 *
	 * Replaces the configuration for the given widget ID.
	 */
	public static function update_widget( string $id, array $definition ): void {
		$id = self::canon_slug( $id );
		if ( ! $id || ! isset( self::$widgets[ $id ] ) ) {
			return;
		}

		if ( isset( $definition['callback'] ) && is_callable( $definition['callback'] ) ) {
			$definition['callback'] = self::normalize_callback( $definition['callback'] );
		}

		self::$widgets[ $id ] = $definition;
		self::$id_map         = null;
	}

	/**
	 * Checks if a widget ID is already registered.
	 *
	 * @param string $id The widget ID to check.
	 *
	 * @return bool True if the ID is registered, false otherwise.
	 */
	private static function is_widget_id_registered( string $id ): bool {
		$id = self::canon_slug( $id );
		return isset( self::$widgets[ $id ] );
	}

	public static function render_widget_fallback( int $user_id = 0 ): string {
		return '<p><strong>' . self::late_i18n( 'Widget callback is missing or invalid.' ) . '</strong></p>';
	}

	private static function late_i18n( string $s ): string {
		return did_action( 'init' ) ? esc_html__( $s, 'artpulse' ) : esc_html( $s );
	}

	private static function include_template( string $template ): string {
		$path = locate_template( $template );
		if ( ! $path ) {
			$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/' . $template;
		}
		if ( file_exists( $path ) ) {
			ob_start();
			include $path;
			return ob_get_clean();
		}

		return '<p>' . self::late_i18n( 'No content available.' ) . '</p>';
	}

	public static function render_widget_news( int $user_id = 0 ): string {
		return self::include_template( 'widgets/widget-news.php' );
	}

	public static function render_widget_events( int $user_id = 0 ): string {
		return self::include_template( 'widgets/events.php' );
	}

	public static function render_widget_favorites( int $user_id = 0 ): string {
		return self::include_template( 'widgets/my-favorites.php' );
	}

	// Legacy aliases used in some configurations.
	public static function render_widget_widget_events( int $user_id = 0 ): string {
		return self::render_widget_events();
	}

	public static function render_widget_widget_favorites( int $user_id = 0 ): string {
		return self::render_widget_favorites();
	}

	public static function render_widget_for_you( int $user_id = 0 ): string {
		return self::include_template( 'widgets/widget-for-you.php' );
	}

	public static function render_widget_nearby_events_map( int $user_id = 0 ): string {
		return self::include_template( 'widgets/nearby-events-map.php' );
	}

	public static function render_widget_my_favorites( int $user_id = 0 ): string {
		return self::include_template( 'widgets/my-favorites.php' );
	}

	public static function render_widget_membership( int $user_id = 0 ): string {
		$billing = function_exists( 'home_url' ) ? home_url( '/account/billing' ) : '';
		if ( ! $billing && function_exists( 'admin_url' ) ) {
			$billing = admin_url( 'profile.php' );
		}
		$upgrade = function_exists( 'home_url' ) ? home_url( '/membership/upgrade' ) : '';
		if ( ! $upgrade && function_exists( 'admin_url' ) ) {
			$upgrade = admin_url( 'admin.php?page=artpulse-membership' );
		}
		$links  = '<a href="' . esc_url( $billing ) . '">Manage Billing</a>';
		$links .= ' <a href="' . esc_url( $upgrade ) . '">Upgrade</a>';
		return '<section data-slug="widget_membership">' . $links . '</section>';
	}

	public static function render_widget_artist_artwork_manager( int $user_id = 0 ): string {
		$url = function_exists( 'admin_url' ) ? admin_url( 'post-new.php?post_type=artpulse_artwork' ) : '#';
		$url = function_exists( 'wp_nonce_url' ) ? wp_nonce_url( $url, 'add_artpulse_artwork' ) : $url;
		return '<section data-slug="widget_artist_artwork_manager"><a href="' . esc_url( $url ) . '">Add New Artwork</a></section>';
	}

	public static function render_widget_artist_feed_publisher( int $user_id = 0 ): string {
		$url = function_exists( 'admin_url' ) ? admin_url( 'post-new.php' ) : '#';
		return '<section data-slug="widget_artist_feed_publisher"><a href="' . esc_url( $url ) . '">Publish</a></section>';
	}

	public static function render_widget_webhooks( int $user_id = 0 ): string {
		$url = function_exists( 'admin_url' ) ? admin_url( 'admin.php?page=artpulse-webhooks' ) : '#';
		return '<section data-slug="widget_webhooks"><a href="' . esc_url( $url ) . '">Manage Webhooks</a></section>';
	}

	/**
	 * Retrieve a widget configuration by ID.
	 */
	public static function get_widget( string $id, int $user_id = 0 ): ?array {
		$id = self::canon_slug( $id );
		if ( ! self::user_can_see( $id, $user_id ) ) {
			return null;
		}
		$widgets = self::get_all();
		return $widgets[ $id ] ?? null;
	}

	/**
	 * Map a builder widget ID to the core widget ID.
	 */
	public static function map_to_core_id( string $id ): string {
		$map = self::get_id_map();
		return $map[ $id ] ?? $id;
	}

	/**
	 * Map a core widget ID back to the builder ID.
	 */
	public static function map_to_builder_id( string $id ): string {
		static $flip = null;
		if ( $flip === null ) {
			$flip = array_flip( self::get_id_map() );
		}

		return $flip[ $id ] ?? $id;
	}

	/**
	 * Return the current builder to core ID map.
	 * Generated on demand from registered widget definitions.
	 */
	public static function get_id_map(): array {
		if ( self::$id_map !== null ) {
			return self::$id_map;
		}

		self::$id_map = self::generate_id_map();

		return self::$id_map;
	}

	/**
	 * Build the mapping between builder IDs and core registry IDs.
	 *
	 * @return array<string,string>
	 */
	private static function generate_id_map(): array {
		$builder = self::get_all( null, true );
		$core    = self::get_definitions();

		$map = array();

		foreach ( $builder as $bid => $bdef ) {
			$label = isset( $bdef['title'] ) ? $bdef['title'] : '';

			if ( isset( $core[ $bid ] ) ) {
				$map[ $bid ] = $bid;
				continue;
			}

			$prefixed = 'widget_' . $bid;
			if ( isset( $core[ $prefixed ] ) ) {
				$map[ $bid ] = $prefixed;
				continue;
			}

			$label_key = sanitize_key( $label );
			$best      = null;
			$best_pct  = 0.0;
			foreach ( $core as $cid => $cdef ) {
				$core_key = sanitize_key( $cdef['name'] ?? $cid );
				if ( $core_key === $label_key ) {
					$best     = $cid;
					$best_pct = 100.0;
					break;
				}
				similar_text( $label_key, $core_key, $pct );
				if ( $pct > $best_pct ) {
					$best_pct = $pct;
					$best     = $cid;
				}
			}

			if ( $best && $best_pct >= 70.0 ) {
				$map[ $bid ] = $best;
			}
		}

		ksort( $map );

		return $map;
	}

	/**
	 * Return all registered widgets.
	 *
	 * @return array<string,array>
	 */
	public static function get_all( ?string $visibility = null, bool $builder = false ): array {
		if ( $builder ) {
			$widgets = self::$builder_widgets;
			if ( $visibility !== null ) {
				$widgets = array_filter(
					$widgets,
					static fn( $w ) => ( $w['visibility'] ?? WidgetVisibility::PUBLIC ) === $visibility
				);
			}

			return $widgets;
		}

		// Start with in-memory registry.
		$widgets = self::$widgets;

		// Merge DB-driven overrides (test writes via update_option()).
		$widgets = self::apply_roles_overrides( $widgets );

		// Filter out widgets disabled by group visibility.
		$group_vis = get_option( 'ap_widget_group_visibility', array() );
		foreach ( $widgets as $id => $cfg ) {
			$grp = $cfg['group'] ?? '';
			if ( $grp && isset( $group_vis[ $grp ] ) && ! $group_vis[ $grp ] ) {
				unset( $widgets[ $id ] );
			}
		}

		// Expose both canonical and unprefixed IDs (helps tests access 'test_widget').
		$widgets = self::expand_legacy_keys( $widgets );

		if ( $visibility !== null ) {
			$widgets = array_filter(
				$widgets,
				static fn( $w ) => ( $w['visibility'] ?? WidgetVisibility::PUBLIC ) === $visibility
			);
		}

		return $widgets;
	}

	/**
	 * Backwards compatibility alias for legacy code.
	 *
	 * @deprecated Use get_all() instead.
	 */
	public static function get_all_widgets( ?string $visibility = null, bool $builder = false ): array {
		return self::get_all( $visibility, $builder );
	}

	/**
	 * Get widgets available for a specific role from the builder registry.
	 */
	public static function get_for_role( string $role ): array {
		$role = sanitize_key( $role );
		return array_filter(
			self::get_all( null, true ),
			static function ( $w ) use ( $role ) {
				$roles = self::normalizeRoleList( $w['roles'] ?? array() );
				return in_array( $role, $roles, true );
			}
		);
	}

	/**
	 * Render a builder widget by ID and return the output.
	 *
	 * @param array{preview_role?:string} $context
	 */
	public static function render( string $id, array $context = array() ): string {
		$id   = self::canon_slug( $id );
		$base = strpos( $id, 'widget_' ) === 0 ? substr( $id, 7 ) : $id;
		$cfg  = self::$builder_widgets[ $id ] ?? self::$builder_widgets[ $base ] ?? null;
		if ( ! $cfg ) {
			return '';
		}
		$preview_role = isset( $context['preview_role'] ) ? (string) $context['preview_role'] : null;
		if ( ! self::user_can_see( $id, 0, $preview_role ) ) {
			return '';
		}

		static $stack = array();
		if ( isset( $stack[ $id ] ) ) {
			return '';
		}
		$stack[ $id ] = true;

		ob_start();
		try {
			call_user_func( $cfg['render_callback'] );
		} catch ( \Throwable $e ) {
			$file = $cfg['file'] ?? 'unknown';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			        error_log( '[DashboardBuilder] Failed rendering widget ' . $id . ' (' . $file . '): ' . $e->getMessage() );
			}
		}
		$html = ob_get_clean();
		unset( $stack[ $id ] );

		return $html;
	}

	/**
	 * Render the preset layout for a role.
	 *
	 * @param string $role Role slug.
	 * @return string
	 */
	public static function render_role_layout( string $role ): string {
	       $slugs = RolePresets::get_preset_slugs( $role );
		if ( ! $slugs ) {
			return '';
		}

		$html     = '';
		$rendered = 0;

		foreach ( $slugs as $slug ) {
			$out = self::render( $slug, array( 'preview_role' => $role ) );
			if ( $out === '' ) {
			        continue;
			}
			$html .= $out;
			++$rendered;
		}

		if ( 0 === $rendered ) {
			$html = self::render( 'widget_placeholder', array( 'preview_role' => $role ) );
		}

		return $html;
	}

	/**
	 * Determine if a user can see a widget.
	 */
	public static function user_can_see( string $id, int $user_id = 0, ?string $preview_role = null ): bool {
		$id = self::canon_slug( $id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$widget = self::get( $id );
		if ( ! $widget ) {
			return false;
		}

                $preview       = $preview_role ? sanitize_key( $preview_role ) : ( \function_exists( '\\get_query_var' ) ? \get_query_var( 'ap_role' ) : null );
                $admin_preview = false;
                if ( \function_exists( '\\current_user_can' ) && \current_user_can( 'manage_options' ) ) {
                        if ( is_string( $preview ) && $preview !== '' ) {
                                $role          = $preview;
                                $admin_preview = true;
                        } else {
                                $role = DashboardController::get_role( $user_id );
                        }
                } else {
                        $role = DashboardController::get_role( $user_id );
                }
		$widget_roles = self::normalizeRoleList( $widget['roles'] ?? array() );
		if ( $widget_roles && ! in_array( $role, $widget_roles, true ) ) {
			return false;
		}

                $cap = $widget['capability'] ?? '';
                if ( ! $admin_preview && $cap && ! \user_can( $user_id, $cap ) ) {
			return false;
		}

		$class = $widget['class'] ?? '';
		if ( ! $admin_preview && $class && method_exists( $class, 'can_view' ) ) {
			try {
				if ( ! call_user_func( array( $class, 'can_view' ), $user_id ) ) {
					return false;
				}
			} catch ( \Throwable $e ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Backwards compatibility alias for user_can_see().
	 */
	public static function isAllowedForCurrentUser( string $id, int $user_id = 0 ): bool {
		return self::user_can_see( $id, $user_id );
	}

	/**
	 * Get a single widget configuration by ID.
	 */
	public static function get( string $id ): ?array {
		$cid     = self::canon_slug( $id );
		$widgets = self::get_all();
		if ( isset( $widgets[ $cid ] ) ) {
			return $widgets[ $cid ];
		}

		$builder = self::get_all( null, true );
		$base    = strpos( $cid, 'widget_' ) === 0 ? substr( $cid, 7 ) : $cid;
		return $builder[ $cid ] ?? $builder[ $base ] ?? null;
	}

	/**
	 * Get widget callbacks allowed for one or more user roles.
	 *
	 * @param string|array $user_role Single role or list of roles.
	 */
	public static function get_widgets( $user_role, int $user_id = 0 ): array {
		$roles   = self::normalizeRoleList( $user_role );
		$allowed = array();

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'ap widget get_widgets roles=%s user=%d', implode( ',', $roles ), $user_id ) );
		}

		foreach ( self::get_all() as $id => $config ) {
			$widget_roles = self::normalizeRoleList( $config['roles'] ?? array() );
			if ( $widget_roles && empty( array_intersect( $roles, $widget_roles ) ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( sprintf( 'ap widget %s excluded: role mismatch', $id ) );
				}
				continue;
			}
			if ( ! self::user_can_see( $id, $user_id ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( sprintf( 'ap widget %s excluded: capability', $id ) );
				}
				continue;
			}
			$allowed[ $id ] = $config['callback'];
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf( 'ap widget %s included', $id ) );
			}
		}

		return $allowed;
	}

	/**
	 * Return full widget definitions.
	 *
	 * @param bool $include_schema Include the settings schema for each widget.
	 */
	public static function get_definitions( bool $include_schema = false, int $user_id = 0 ): array {
		$defs = array();
		foreach ( self::get_all() as $id => $config ) {
			if ( ! self::user_can_see( $id, $user_id ) ) {
				continue;
			}
			// Sanitize widget configuration to avoid undefined index warnings.
			$label       = isset( $config['label'] ) ? $config['label'] : 'Unnamed Widget';
			$icon        = isset( $config['icon'] ) ? $config['icon'] : 'dashicons-admin-generic';
			$description = isset( $config['description'] ) ? $config['description'] : '';
			$def         = array(
				'id'          => $id,
				'name'        => self::late_i18n( $label ),
				'icon'        => $icon,
				'description' => self::late_i18n( $description ),
			);
			if ( isset( $config['category'] ) ) {
				$def['category'] = $config['category'];
			}
			if ( isset( $config['roles'] ) ) {
				$def['roles'] = $config['roles'];
			}
			if ( isset( $config['capability'] ) ) {
				$def['capability'] = $config['capability'];
			}
			if ( isset( $config['tags'] ) ) {
				$def['tags'] = $config['tags'];
			}
			if ( $include_schema ) {
				$def['settings'] = $config['settings'] ?? array();
			}
			$defs[ $id ] = $def;
		}

		return apply_filters( 'ap_dashboard_widget_definitions', $defs );
	}

	/**
	 * Get a single widget callback by ID.
	 */
	public static function get_widget_callback( string $id, int $user_id = 0 ): ?callable {
		$id = self::canon_slug( $id );
		if ( ! self::user_can_see( $id, $user_id ) ) {
			return null;
		}
		$widgets = self::get_all();
		return $widgets[ $id ]['callback'] ?? null;
	}

	/**
	 * Get the settings schema for a widget.
	 */
	public static function get_widget_schema( string $id, int $user_id = 0 ): array {
		$id = self::canon_slug( $id );
		if ( ! self::user_can_see( $id, $user_id ) ) {
			return array();
		}
		$widgets = self::get_all();
		return $widgets[ $id ]['settings'] ?? array();
	}

	/**
	 * Get widgets definitions filtered by one or more roles.
	 *
	 * @param string|array $role Single role or array of roles.
	 */
	public static function get_widgets_by_role( $role, int $user_id = 0 ): array {
		$roles = self::normalizeRoleList( $role );
		$defs  = array();
		foreach ( self::get_all() as $id => $cfg ) {
			$widget_roles = self::normalizeRoleList( $cfg['roles'] ?? array() );
			if ( $widget_roles && empty( array_intersect( $roles, $widget_roles ) ) ) {
				continue;
			}
			if ( ! self::user_can_see( $id, $user_id ) ) {
				continue;
			}
			$defs[ $id ] = $cfg;
		}

		return $defs;
	}

	/**
	 * Build a map of widgets grouped by role.
	 *
	 * Each entry contains the widget ID along with optional callback and REST
	 * configuration, matching the structure expected by
	 * {@see \ArtPulse\Admin\DashboardWidgetTools::get_role_widgets()}.
	 *
	 * @param array $roles Optional list of roles to include. Defaults to all roles.
	 * @return array<string,array<int,array<string,mixed>>>
	 */
	public static function get_role_widget_map( array $roles = array() ): array {
		if ( ! $roles ) {
			if ( function_exists( 'wp_roles' ) ) {
				$roles = array_keys( wp_roles()->roles );
			} else {
				$roles = array( 'member', 'artist', 'organization' );
			}
		} else {
			$roles = array_map( 'sanitize_key', $roles );
		}

		$map = array_fill_keys( $roles, array() );

		foreach ( self::get_all() as $id => $def ) {
			$item = array( 'id' => $id );
			if ( ! empty( $def['callback'] ) ) {
				$item['callback'] = $def['callback'];
			}
			if ( ! empty( $def['rest'] ) ) {
				$item['rest'] = $def['rest'];
			}

			$widget_roles = self::normalizeRoleList( $def['roles'] ?? array() );
			if ( $widget_roles ) {
				foreach ( $widget_roles as $role ) {
					$role = sanitize_key( $role );
					if ( ! isset( $map[ $role ] ) ) {
						$map[ $role ] = array();
					}
					$map[ $role ][] = $item;
				}
			} else {
				foreach ( $roles as $role ) {
					$map[ $role ][] = $item;
				}
			}
		}

		return $map;
	}

	/**
	 * Get a random subset of widgets for a role.
	 */
	public static function get_random( string $role, int $limit = 1, int $user_id = 0 ): array {
		$widgets = self::get_widgets_by_role( $role, $user_id );
		if ( ! $widgets ) {
			return array();
		}
		$keys = array_keys( $widgets );
		shuffle( $keys );
		$keys = array_slice( $keys, 0, $limit );

		return array_intersect_key( $widgets, array_flip( $keys ) );
	}

	/**
	 * Get widgets that belong to a specific category.
	 */
	public static function get_by_category( string $category, int $user_id = 0 ): array {
		return array_filter(
			self::get_all(),
			static function ( $cfg, $id ) use ( $category, $user_id ) {
				if ( isset( $cfg['category'] ) && $cfg['category'] === $category ) {
					return DashboardWidgetRegistry::user_can_see( $id, $user_id );
				}
				return false;
			},
			ARRAY_FILTER_USE_BOTH
		);
	}

	/**
	 * Register default widgets and fire registration hook.
	 */
	public static function init(): void {
		static $initialized = false;
		if ( $initialized ) {
			return;
		}
		$initialized = true;

		// Map legacy widget slugs to their canonical identifiers before
		// loading widgets so that earlier registrations are merged
		// correctly.
		self::alias( 'myevents', 'widget_my_events' );
		self::alias( 'widget_myevents', 'widget_my_events' );

		$loader_file = dirname( __DIR__, 2 ) . '/includes/widget-loader.php';
		if ( ! class_exists( DashboardWidgetRegistryLoader::class ) && file_exists( $loader_file ) ) {
			require_once $loader_file;
		}
		if ( class_exists( DashboardWidgetRegistryLoader::class ) ) {
			DashboardWidgetRegistryLoader::load_all();
		}

		$register = array( self::class, 'register_widget' );
		$register(
			'widget_news',
			array(
				'id'          => 'widget_news',
				'label'       => 'News',
				'icon'        => 'dashicons-megaphone',
				'description' => 'Latest updates from ArtPulse.',
				'callback'    => array( self::class, 'render_widget_news' ),
				'roles'       => array( 'member' ),
				'visibility'  => WidgetVisibility::PUBLIC,
			)
		);
		$register(
			'widget_events',
			array(
				'id'          => 'widget_events',
				'label'       => 'Upcoming Events (Member)',
				'icon'        => 'dashicons-calendar-alt',
				'description' => 'Events happening soon.',
				'callback'    => array( self::class, 'render_widget_events' ),
				'roles'       => array( 'member', 'organization' ),
			)
		);
		$register(
			'widget_favorites',
			array(
				'id'          => 'widget_favorites',
				'label'       => 'Favorites Overview',
				'icon'        => 'dashicons-star-filled',
				'description' => 'Artists you have saved.',
				'callback'    => array( self::class, 'render_widget_favorites' ),
				'roles'       => array( 'member' ),
			)
		);
		$register(
			'widget_for_you_member',
			array(
				'id'          => 'widget_for_you_member',
				'label'       => 'For You (Member)',
				'icon'        => 'dashicons-thumbs-up',
				'description' => 'Recommended content.',
				'callback'    => array( self::class, 'render_widget_for_you' ),
				'roles'       => array( 'member', 'artist' ),
			)
		);
		$register(
			'widget_nearby_events_map',
			array(
				'id'          => 'widget_nearby_events_map',
				'label'       => 'Nearby Events',
				'icon'        => 'dashicons-location-alt',
				'description' => 'Events around your location.',
				'callback'    => array( self::class, 'render_widget_nearby_events_map' ),
				'roles'       => array( 'member', 'artist' ),
			)
		);
		$register(
			'widget_my_favorites',
			array(
				'id'          => 'widget_my_favorites',
				'label'       => 'My Favorite Events',
				'icon'        => 'dashicons-star-empty',
				'description' => 'Your saved events.',
				'callback'    => array( self::class, 'render_widget_my_favorites' ),
				'roles'       => array( 'member', 'artist' ),
			)
		);

		if ( ! did_action( 'artpulse_register_dashboard_widget' ) ) {
			do_action( 'artpulse_register_dashboard_widget' );
		}
	}

	/**
	 * Render all widgets visible to the specified user in a basic grid layout.
	 */
       public static function render_for_role( int $user_id ): void {
	       $role = DashboardController::get_role( $user_id );

	       do_action( 'ap_before_widgets', $role );

	       $layout  = UserLayoutManager::get_layout_for_user( $user_id );
	       $widgets = apply_filters( 'ap_dashboard_widgets', self::get_widgets_by_role( $role, $user_id ), $role );

	       $debug = defined( 'WP_DEBUG' ) && WP_DEBUG;

	       $sections = array();
	       $order    = array();
	       $rendered = 0;

	       foreach ( $layout as $row ) {
		       $id      = self::canon_slug( $row['id'] ?? '' );
		       if ( ! $id ) {
			       continue;
		       }
		       $cfg        = $widgets[ $id ] ?? null;
		       $can_render = $cfg && self::user_can_see( $id, $user_id ) && is_callable( $cfg['callback'] ?? null );
		       $section    = '';
		       if ( $cfg ) {
			       $class   = $cfg['class'] ?? '';
			       $section = $cfg['section'] ?? '';
			       if ( $class && method_exists( $class, 'get_section' ) ) {
			               try {
			                       $section = call_user_func( array( $class, 'get_section' ) );
			               } catch ( \Throwable $e ) {
			                       $section = '';
			               }
			       }
			       $section = sanitize_key( $section );
		       }
		       if ( ! isset( $sections[ $section ] ) ) {
			       $sections[ $section ] = array();
			       $order[]              = $section;
		       }

		       $html = '';
		       if ( $can_render ) {
			       try {
			               ob_start();
			               $result = call_user_func( $cfg['callback'], $user_id );
			               $echoed = ob_get_clean();
			               $html   = is_string( $result ) && '' !== $result ? $result : $echoed;
			       } catch ( \Throwable $e ) {
			               ob_end_clean();
			               if ( $debug ) {
			                       error_log( 'Widget ' . $id . ' failed: ' . $e->getMessage() );
			               }
			       }
		       }
		       if ( $html === '' ) {
			       ob_start();
			       ApPlaceholderWidget::render( $user_id );
			       $html = ob_get_clean();
		       }

		       $sections[ $section ][] = '<div class="postbox" data-slug="' . esc_attr( $id ) . '"><div class="inside">' . $html . '</div></div>';
		       ++$rendered;
	       }

	       foreach ( $order as $sec ) {
		       echo '<section class="ap-widget-section">';
		       if ( $sec ) {
			       echo '<h2>' . self::late_i18n( ucfirst( $sec ) ) . '</h2>';
		       }
		       echo '<div class="meta-box-sortables">';
		       foreach ( $sections[ $sec ] as $html ) {
			       echo $html;
		       }
		       echo '</div></section>';
	       }

	       if ( 0 === $rendered ) {
		       echo '<div class="ap-dashboard-error">' . self::late_i18n( 'No dashboard widgets could be rendered.' ) . '</div>';
		       if ( $debug ) {
			       error_log( "[AP Dashboard] No widgets rendered for role {$role}" );
		       }
	       } elseif ( $debug ) {
		       error_log( "[AP Dashboard] Rendered {$rendered} widgets for role {$role}" );
	       }

	       do_action( 'ap_after_widgets', $role );
       }

	/**
	 * Merge roles/capability overrides from the `artpulse_widget_roles` option.
	 *
	 * Shape:
	 * [
	 *   'widget_id' => [
	 *     'roles'      => array<string>|string|null,
	 *     'capability' => string,
	 *     'exclude_roles' => array<string>|string|null,
	 *   ],
	 *   ...
	 * ]
	 */
	private static function apply_roles_overrides( array $widgets ): array {
		$settings = get_option( 'artpulse_widget_roles', array() );
		if ( is_string( $settings ) ) {
			$decoded  = json_decode( $settings, true );
			$settings = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $settings ) ) {
			return $widgets;
		}

		foreach ( $settings as $raw_id => $cfg ) {
			$id = self::canon_slug( (string) $raw_id );
			if ( ! isset( $widgets[ $id ] ) || ! is_array( $cfg ) ) {
				continue;
			}

			if ( isset( $cfg['roles'] ) ) {
			        $normalized = self::normalizeRoleList( $cfg['roles'] );
			        if ( $normalized !== array() ) {
			                $widgets[ $id ]['roles'] = $normalized;
			        }
			}
			if ( array_key_exists( 'capability', $cfg ) ) {
				$widgets[ $id ]['capability'] = sanitize_key( (string) $cfg['capability'] );
			}
			if ( array_key_exists( 'exclude_roles', $cfg ) ) {
				$widgets[ $id ]['exclude_roles'] = self::normalizeRoleList( $cfg['exclude_roles'] );
			}
		}

		return $widgets;
	}

	/**
	 * Ensure returned array exposes both canonical and unprefixed keys.
	 * Example: 'widget_foo' will also be returned as 'foo' if not already present.
	 */
	private static function expand_legacy_keys( array $widgets ): array {
		$extra = array();
		foreach ( $widgets as $id => $cfg ) {
			if ( strpos( $id, 'widget_' ) === 0 ) {
				$alt = substr( $id, 7 );
				if ( $alt !== '' && ! isset( $widgets[ $alt ] ) ) {
					$extra[ $alt ] = $cfg;
				}
			} else {
				$alt = 'widget_' . $id;
				if ( ! isset( $widgets[ $alt ] ) ) {
					$extra[ $alt ] = $cfg;
				}
			}
		}
		return $widgets + $extra;
	}
}
