<?php

namespace ArtPulse\Core;

use ArtPulse\DashboardWidgetRegistryLoader;
use ArtPulse\Dashboard\WidgetVisibility;
use WP_Roles;
use ArtPulse\Admin\UserLayoutManager;

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
    private static array $widgets = [];

    /**
     * Builder widget definitions used by the dashboard builder UI.
     *
     * @var array<string,array>
     */
    private static array $builder_widgets = [];

    /**
     * Cached mapping of builder IDs to core IDs.
     *
     * @var array<string,string>|null
     */
    private static ?array $id_map = null;

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
     * Register a widget and its settings.
     *
     * @param callable $callback Callback used to render the widget. Must be
     *                           callable.
     */
    public static function register(
        string $id,
        string|array $label,
        string $icon = '',
        string $description = '',
        callable $callback = null,
        array $options = [] // supports 'category', 'roles', optional 'settings' and 'tags'
    ): void {
        // Builder-style registration when the second argument is an array.
        if ( is_array( $label ) ) {
            $id = sanitize_key( $id );
            if ( ! $id ) {
                return;
            }
            $args = array_merge(
                [
                    'title'           => '',
                    'render_callback' => null,
                    'roles'           => [],
                    'file'            => '',
                    'visibility'      => WidgetVisibility::PUBLIC,
                ],
                $label
            );

            if ( ! is_callable( $args['render_callback'] ) ) {
                $args['render_callback'] = static function () {};
            }
            $visibility = in_array( $args['visibility'], WidgetVisibility::values(), true )
                ? $args['visibility']
                : WidgetVisibility::PUBLIC;

            self::$builder_widgets[ $id ] = [
                'id'             => $id,
                'title'          => (string) $args['title'],
                'render_callback'=> $args['render_callback'],
                'roles'          => array_map( 'sanitize_key', (array) $args['roles'] ),
                'file'           => (string) $args['file'],
                'visibility'     => $visibility,
            ];

            return;
        }

        // Core-style registration.
        if ( self::is_widget_id_registered( $id ) ) {
            trigger_error( 'Dashboard widget ID already registered: ' . $id, E_USER_WARNING );

            return;
        }
        $label = trim( $label );
        if ( self::is_widget_label_registered( $label ) ) {
            trigger_error( 'Dashboard widget label already registered: ' . $label, E_USER_WARNING );

            return;
        }

        if ( ! is_callable( $callback ) ) {
            error_log( 'Invalid dashboard widget callback for ID ' . $id );
            $callback = [ self::class, 'render_widget_fallback' ];
        }

        $callback = self::normalize_callback( $callback );

        $class = '';
        if ( is_array( $callback ) && isset( $callback[0] ) && is_string( $callback[0] ) ) {
            $class = $callback[0];
        }

        self::$widgets[ $id ] = [
            'label'       => $label,
            'icon'        => $icon,
            'description' => $description,
            'callback'    => $callback,
            'class'       => $class,
            'category'    => $options['category'] ?? '',
            'group'       => $options['group'] ?? '',
            'section'     => $options['section'] ?? '',
            'roles'       => $options['roles'] ?? [],
            'settings'    => $options['settings'] ?? [],
            'tags'        => $options['tags'] ?? [],
            'capability'  => $options['capability'] ?? '',
            'cache'       => $options['cache'] ?? false,
            'lazy'        => $options['lazy'] ?? false,
            'visibility'  => $options['visibility'] ?? WidgetVisibility::PUBLIC,
            'builder_only'=> $options['builder_only'] ?? false,
        ];
    }

    /**
     * Simplified widget registration used by generic dashboards.
     * Supports all options from register(), including optional 'tags'.
     */
    public static function register_widget( string $id, array $args ): void {
        $id = sanitize_key( $id );
        if ( ! $id ) {
            return;
        }

        if ( self::is_widget_id_registered( $id ) ) {
            trigger_error( 'Dashboard widget ID already registered: ' . $id, E_USER_WARNING );

            return;
        }

        $label = trim( $args['label'] ?? 'Untitled' );
        if ( self::is_widget_label_registered( $label ) ) {
            trigger_error( 'Dashboard widget label already registered: ' . $label, E_USER_WARNING );

            return;
        }

        $args['label'] = $label;
        $args['group'] = $args['group'] ?? '';
        $args['section'] = $args['section'] ?? '';

        if ( empty( $args['callback'] ) && isset( $args['template'] ) ) {
            $template = $args['template'];
            $path     = locate_template( $template );
            if ( ! $path ) {
                $path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $template;
            }
            if ( ! file_exists( $path ) ) {
                do_action( 'ap_widget_missing_template', $id, $template );
                return;
            }
            $args['callback'] = static function () use ( $path ) {
                ob_start();
                include $path;
                return ob_get_clean();
            };
        }

        if ( empty( $args['callback'] ) || ! is_callable( $args['callback'] ) ) {
            $args['callback'] = [ self::class, 'render_widget_fallback' ];
        } else {
            $args['callback'] = self::normalize_callback( $args['callback'] );
        }

        $class = '';
        if ( is_array( $args['callback'] ) && isset( $args['callback'][0] ) && is_string( $args['callback'][0] ) ) {
            $class = $args['callback'][0];
        }

        $args['id']    = $id;
        $args['class'] = $class;
        self::$widgets[ $id ] = $args;
    }

    /**
     * Update an existing widget definition.
     *
     * Replaces the configuration for the given widget ID.
     */
    public static function update_widget( string $id, array $definition ): void {
        $id = sanitize_key( $id );
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
        return isset( self::$widgets[ $id ] );
    }

    /**
     * Checks if a widget label is already registered.
     *
     * @param string $label The widget label to check.
     *
     * @return bool True if the label is registered, false otherwise.
     */
    private static function is_widget_label_registered( string $label ): bool {
        $label = strtolower( trim( $label ) );
        foreach ( self::$widgets as $w ) {
            if ( strtolower( trim( $w['label'] ?? '' ) ) === $label ) {
                return true;
            }
        }

        return false;
    }

    public static function render_widget_fallback( int $user_id = 0 ): string {
        return '<p><strong>Widget callback is missing or invalid.</strong></p>';
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

        return '<p>' . esc_html__( 'No content available.', 'artpulse' ) . '</p>';
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

    /**
     * Retrieve a widget configuration by ID.
     */
    public static function get_widget( string $id, int $user_id = 0 ): ?array {
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

        $map = [];

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
                    $best = $cid;
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
    public static function get_all(?string $visibility = null, bool $builder = false): array {
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

        $widgets  = self::$widgets;
        $settings = get_option('artpulse_widget_roles', []);
        foreach ($settings as $id => $cfg) {
            if (!isset($widgets[$id])) {
                continue;
            }

            // Backward compatibility: allow storing roles as a simple array.
            if (is_array($cfg) && array_keys($cfg) === range(0, count($cfg) - 1)) {
                $widgets[$id]['roles'] = array_map('sanitize_key', $cfg);
                continue;
            }

            if (isset($cfg['roles'])) {
                $widgets[$id]['roles'] = array_map('sanitize_key', (array) $cfg['roles']);
            }
            if (isset($cfg['capability'])) {
                $widgets[$id]['capability'] = sanitize_key($cfg['capability']);
            }
        }
        $group_vis = get_option('ap_widget_group_visibility', []);
        foreach ($widgets as $id => $cfg) {
            $grp = $cfg['group'] ?? '';
            if ($grp && isset($group_vis[$grp]) && !$group_vis[$grp]) {
                unset($widgets[$id]);
            }
        }

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
    public static function get_all_widgets(?string $visibility = null, bool $builder = false): array {
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
                $roles = isset( $w['roles'] ) ? (array) $w['roles'] : [];
                return in_array( $role, $roles, true );
            }
        );
    }

    /**
     * Render a builder widget by ID and return the output.
     */
    public static function render( string $id ): string {
        if ( ! isset( self::$builder_widgets[ $id ] ) ) {
            return '';
        }

        static $stack = [];
        if ( isset( $stack[ $id ] ) ) {
            return '';
        }
        $stack[ $id ] = true;

        ob_start();
        try {
            call_user_func( self::$builder_widgets[ $id ]['render_callback'] );
        } catch ( \Throwable $e ) {
            $file = self::$builder_widgets[ $id ]['file'] ?? 'unknown';
            error_log( '[DashboardBuilder] Failed rendering widget ' . $id . ' (' . $file . '): ' . $e->getMessage() );
        }
        $html = ob_get_clean();
        unset( $stack[ $id ] );

        return $html;
    }

    /**
     * Determine if a user can see a widget.
     */
    public static function user_can_see( string $id, int $user_id = 0 ): bool {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $widget = self::get( $id );
        if ( ! $widget ) {
            return false;
        }

        $role         = DashboardController::get_role( $user_id );
        $widget_roles = isset( $widget['roles'] ) ? (array) $widget['roles'] : [];
        if ( $widget_roles && ! in_array( $role, $widget_roles, true ) ) {
            return false;
        }

        $cap = $widget['capability'] ?? '';
        if ( $cap && ! user_can( $user_id, $cap ) ) {
            return false;
        }

        $class = $widget['class'] ?? '';
        if ( $class && method_exists( $class, 'can_view' ) ) {
            try {
                if ( ! call_user_func( [ $class, 'can_view' ], $user_id ) ) {
                    return false;
                }
            } catch ( \Throwable $e ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a single widget configuration by ID.
     */
    public static function get( string $id ): ?array {
        $widgets = self::get_all();
        if ( isset( $widgets[ $id ] ) ) {
            return $widgets[ $id ];
        }

        $builder = self::get_all( null, true );
        return $builder[ $id ] ?? null;
    }

    /**
     * Get widget callbacks allowed for a user role.
     */
    /**
     * Get widget callbacks allowed for one or more user roles.
     *
     * @param string|array $user_role Single role or list of roles.
     */
    public static function get_widgets( $user_role, int $user_id = 0 ): array {
        $roles   = array_map( 'sanitize_key', (array) $user_role );
        $allowed = [];
        foreach ( self::get_all() as $id => $config ) {
            $widget_roles = isset( $config['roles'] ) ? (array) $config['roles'] : [];
            if ( $widget_roles && empty( array_intersect( $roles, $widget_roles ) ) ) {
                continue;
            }
            if ( ! self::user_can_see( $id, $user_id ) ) {
                continue;
            }
            $allowed[ $id ] = $config['callback'];
        }

        return $allowed;
    }

    /**
     * Return full widget definitions.
     *
     * @param bool $include_schema Include the settings schema for each widget.
     */
    public static function get_definitions( bool $include_schema = false, int $user_id = 0 ): array {
        $defs = [];
        foreach ( self::get_all() as $id => $config ) {
            if ( ! self::user_can_see( $id, $user_id ) ) {
                continue;
            }
            // Sanitize widget configuration to avoid undefined index warnings.
            $label       = isset( $config['label'] ) ? $config['label'] : 'Unnamed Widget';
            $icon        = isset( $config['icon'] ) ? $config['icon'] : 'dashicons-admin-generic';
            $description = isset( $config['description'] ) ? $config['description'] : '';
            $def         = [
                'id'          => $id,
                'name'        => $label,
                'icon'        => $icon,
                'description' => $description,
            ];
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
                $def['settings'] = $config['settings'] ?? [];
            }
            $defs[ $id ] = $def;
        }

        return apply_filters( 'ap_dashboard_widget_definitions', $defs );
    }

    /**
     * Get a single widget callback by ID.
     */
    public static function get_widget_callback( string $id, int $user_id = 0 ): ?callable {
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
        if ( ! self::user_can_see( $id, $user_id ) ) {
            return [];
        }
        $widgets = self::get_all();
        return $widgets[ $id ]['settings'] ?? [];
    }

    /**
     * Get widgets definitions filtered by one or more roles.
     *
     * @param string|array $role Single role or array of roles.
     */
    public static function get_widgets_by_role( $role, int $user_id = 0 ): array {
        $roles = array_map( 'sanitize_key', (array) $role );
        $defs  = [];
        foreach ( self::get_all() as $id => $cfg ) {
            $widget_roles = isset( $cfg['roles'] ) ? (array) $cfg['roles'] : [];
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
    public static function get_role_widget_map( array $roles = [] ): array {
        if ( ! $roles ) {
            if ( function_exists( 'wp_roles' ) ) {
                $roles = array_keys( wp_roles()->roles );
            } else {
                $roles = [ 'member', 'artist', 'organization' ];
            }
        } else {
            $roles = array_map( 'sanitize_key', $roles );
        }

        $map = array_fill_keys( $roles, [] );

        foreach ( self::get_all() as $id => $def ) {
            $item = [ 'id' => $id ];
            if ( ! empty( $def['callback'] ) ) {
                $item['callback'] = $def['callback'];
            }
            if ( ! empty( $def['rest'] ) ) {
                $item['rest'] = $def['rest'];
            }

            $widget_roles = isset( $def['roles'] ) ? (array) $def['roles'] : [];
            if ( $widget_roles ) {
                foreach ( $widget_roles as $role ) {
                    $role = sanitize_key( $role );
                    if ( ! isset( $map[ $role ] ) ) {
                        $map[ $role ] = [];
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
            return [];
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
        DashboardWidgetRegistryLoader::load_all();

        $register = [ self::class, 'register_widget' ];
        $register( 'widget_news', [
            'id'          => 'widget_news',
            'label'       => __( 'News', 'artpulse' ),
            'icon'        => 'dashicons-megaphone',
            'description' => __( 'Latest updates from ArtPulse.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_news' ],
            'roles'       => [ 'member' ],
            'visibility'  => WidgetVisibility::PUBLIC,
        ] );
        $register( 'widget_events', [
            'id'          => 'widget_events',
            'label'       => __( 'Upcoming Events (Member)', 'artpulse' ),
            'icon'        => 'dashicons-calendar-alt',
            'description' => __( 'Events happening soon.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_events' ],
            'roles'       => [ 'member', 'organization' ],
        ] );
        $register( 'widget_favorites', [
            'id'          => 'widget_favorites',
            'label'       => __( 'Favorites Overview', 'artpulse' ),
            'icon'        => 'dashicons-star-filled',
            'description' => __( 'Artists you have saved.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_favorites' ],
            'roles'       => [ 'member' ],
        ] );
        $register( 'widget_for_you_member', [
            'id'          => 'widget_for_you_member',
            'label'       => __( 'For You (Member)', 'artpulse' ),
            'icon'        => 'dashicons-thumbs-up',
            'description' => __( 'Recommended content.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_for_you' ],
            'roles'       => [ 'member', 'artist' ],
        ] );
        $register( 'widget_nearby_events_map', [
            'id'          => 'widget_nearby_events_map',
            'label'       => __( 'Nearby Events', 'artpulse' ),
            'icon'        => 'dashicons-location-alt',
            'description' => __( 'Events around your location.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_nearby_events_map' ],
            'roles'       => [ 'member', 'artist' ],
        ] );
        $register( 'widget_my_favorites', [
            'id'          => 'widget_my_favorites',
            'label'       => __( 'My Favorite Events', 'artpulse' ),
            'icon'        => 'dashicons-star-empty',
            'description' => __( 'Your saved events.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_my_favorites' ],
            'roles'       => [ 'member', 'artist' ],
        ] );
        do_action( 'artpulse_register_dashboard_widget' );
    }

    /**
     * Render all widgets visible to the specified user in a basic grid layout.
     */
    public static function render_for_role( int $user_id ): void {
        $role    = DashboardController::get_role( $user_id );

        do_action( 'ap_before_widgets', $role );

        $result  = UserLayoutManager::get_role_layout( $role );
        $layout  = $result['layout'];
        $widgets = apply_filters( 'ap_dashboard_widgets', self::get_widgets_by_role( $role, $user_id ), $role );

        $layout_ids = array_map(
            static fn( $row ) => sanitize_key( $row['id'] ?? '' ),
            $layout
        );
        $widget_ids = array_keys( $widgets );

        $missing = array_diff( $layout_ids, $widget_ids );
        foreach ( $missing as $id ) {
            error_log( "[AP Dashboard] Widget {$id} missing for role {$role}" );
        }
        $extra = array_diff( $widget_ids, $layout_ids );
        foreach ( $extra as $id ) {
            error_log( "[AP Dashboard] Widget {$id} not in layout for role {$role}" );
        }

        $sections = [];
        $order    = [];
        foreach ( $layout_ids as $id ) {
            if ( ! isset( $widgets[ $id ] ) || ! self::user_can_see( $id, $user_id ) ) {
                continue;
            }
            $cfg     = $widgets[ $id ];
            $class   = $cfg['class'] ?? '';
            $section = $cfg['section'] ?? '';
            if ( $class && method_exists( $class, 'get_section' ) ) {
                try {
                    $section = call_user_func( [ $class, 'get_section' ] );
                } catch ( \Throwable $e ) {
                    $section = '';
                }
            }
            $section = sanitize_key( $section );
            if ( ! isset( $sections[ $section ] ) ) {
                $sections[ $section ] = [];
                $order[]              = $section;
            }
            $sections[ $section ][] = $cfg + [ 'id' => $id ];
        }

        foreach ( $order as $sec ) {
            echo '<section class="ap-widget-section">';
            if ( $sec ) {
                echo '<h2>' . esc_html( ucfirst( $sec ) ) . '</h2>';
            }
            foreach ( $sections[ $sec ] as $cfg ) {
                try {
                    ob_start();
                    $result = call_user_func( $cfg['callback'], $user_id );
                    $echoed = ob_get_clean();
                    if ( is_string( $result ) && '' !== $result ) {
                        echo $result;
                    } else {
                        echo $echoed;
                    }
                } catch ( \Throwable $e ) {
                    ob_end_clean();
                    error_log( 'Widget ' . $cfg['id'] . ' failed: ' . $e->getMessage() );
                }
            }
            echo '</section>';
        }

        do_action( 'ap_after_widgets', $role );
    }
}
