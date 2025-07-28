<?php

namespace ArtPulse\Core;

use WP_Roles;

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
     *     capability?:string
     * }>
     */
    private static array $widgets = [];

    /**
     * Cached mapping of builder IDs to core IDs.
     *
     * @var array<string,string>|null
     */
    private static ?array $id_map = null;

    /**
     * Register a widget and its settings.
     *
     * @param callable $callback Callback used to render the widget. Must be
     *                           callable.
     */
    public static function register(
        string $id,
        string $label,
        string $icon,
        string $description,
        callable $callback,
        array $options = [] // supports 'category', 'roles', optional 'settings' and 'tags'
    ): void {
        // Prevent duplicate IDs or labels.
        if ( self::is_widget_id_registered( $id ) ) {
            trigger_error( 'Dashboard widget ID already registered: ' . $id, E_USER_WARNING );

            return;
        }
        if ( self::is_widget_label_registered( $label ) ) {
            trigger_error( 'Dashboard widget label already registered: ' . $label, E_USER_WARNING );

            return;
        }

        // Callback must be valid to render the widget.
        if ( ! is_callable( $callback ) ) {
            error_log( 'Invalid dashboard widget callback for ID ' . $id );
            $callback = [ self::class, 'render_widget_fallback' ];
        }

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
            'roles'       => $options['roles'] ?? [],
            'settings'    => $options['settings'] ?? [],
            'tags'        => $options['tags'] ?? [],
            'capability'  => $options['capability'] ?? '',
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

        $label = $args['label'] ?? 'Untitled';
        if ( self::is_widget_label_registered( $label ) ) {
            trigger_error( 'Dashboard widget label already registered: ' . $label, E_USER_WARNING );

            return;
        }

        $args['label'] = $label;

        if ( empty( $args['callback'] ) && isset( $args['template'] ) ) {
            $template         = $args['template'];
            $args['callback'] = static function () use ( $template ) {
                $path = locate_template( $template );
                if ( ! $path ) {
                    $path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $template;
                }
                if ( file_exists( $path ) ) {
                    include $path;
                }
            };
        }

        if ( empty( $args['callback'] ) || ! is_callable( $args['callback'] ) ) {
            $args['callback'] = [ self::class, 'render_widget_fallback' ];
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
        foreach ( self::$widgets as $w ) {
            if ( ( $w['label'] ?? '' ) === $label ) {
                return true;
            }
        }

        return false;
    }

    public static function render_widget_fallback(): void {
        echo '<p><strong>Widget callback is missing or invalid.</strong></p>';
    }

    private static function include_template( string $template ): void {
        $path = locate_template( $template );
        if ( ! $path ) {
            $path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/' . $template;
        }
        if ( file_exists( $path ) ) {
            include $path;
        } else {
            echo '<p>' . esc_html__( 'No content available.', 'artpulse' ) . '</p>';
        }
    }

    public static function render_widget_news(): void {
        self::include_template( 'widgets/widget-news.php' );
    }

    public static function render_widget_events(): void {
        self::include_template( 'widgets/events.php' );
    }

    public static function render_widget_favorites(): void {
        self::include_template( 'widgets/my-favorites.php' );
    }

    public static function render_widget_for_you(): void {
        self::include_template( 'widgets/widget-for-you.php' );
    }

    public static function render_widget_nearby_events_map(): void {
        self::include_template( 'widgets/nearby-events-map.php' );
    }

    public static function render_widget_my_favorites(): void {
        self::include_template( 'widgets/my-favorites.php' );
    }

    /**
     * Retrieve a widget configuration by ID.
     */
    public static function get_widget( string $id ): ?array {
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
        $builder = \ArtPulse\DashboardBuilder\DashboardWidgetRegistry::get_all();
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
    public static function get_all(): array {
        $widgets  = self::$widgets;
        $settings = get_option('ap_widget_visibility_settings', []);
        foreach ($settings as $id => $cfg) {
            if (!isset($widgets[$id])) {
                continue;
            }
            if (isset($cfg['roles'])) {
                $widgets[$id]['roles'] = array_map('sanitize_key', (array) $cfg['roles']);
            }
            if (isset($cfg['capability'])) {
                $widgets[$id]['capability'] = sanitize_key($cfg['capability']);
            }
        }
        return $widgets;
    }

    /**
     * Get a single widget configuration by ID.
     */
    public static function get( string $id ): ?array {
        $widgets = self::get_all();
        return $widgets[ $id ] ?? null;
    }

    /**
     * Get widget callbacks allowed for a user role.
     */
    /**
     * Get widget callbacks allowed for one or more user roles.
     *
     * @param string|array $user_role Single role or list of roles.
     */
    public static function get_widgets( $user_role ): array {
        $roles   = array_map( 'sanitize_key', (array) $user_role );
        $allowed = [];
        foreach ( self::get_all() as $id => $config ) {
            $widget_roles = isset( $config['roles'] ) ? (array) $config['roles'] : [];
            if ( $widget_roles && empty( array_intersect( $roles, $widget_roles ) ) ) {
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
    public static function get_definitions( bool $include_schema = false ): array {
        $defs = [];
        foreach ( self::get_all() as $id => $config ) {
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
    public static function get_widget_callback( string $id ): ?callable {
        $widgets = self::get_all();
        return $widgets[ $id ]['callback'] ?? null;
    }

    /**
     * Get the settings schema for a widget.
     */
    public static function get_widget_schema( string $id ): array {
        $widgets = self::get_all();
        return $widgets[ $id ]['settings'] ?? [];
    }

    /**
     * Get widgets definitions filtered by one or more roles.
     *
     * @param string|array $role Single role or array of roles.
     */
    public static function get_widgets_by_role( $role ): array {
        $roles = array_map( 'sanitize_key', (array) $role );
        $defs  = [];
        foreach ( self::get_all() as $id => $cfg ) {
            $widget_roles = isset( $cfg['roles'] ) ? (array) $cfg['roles'] : [];
            if ( $widget_roles && empty( array_intersect( $roles, $widget_roles ) ) ) {
                continue;
            }
            $defs[ $id ] = $cfg;
        }

        return $defs;
    }

    /**
     * Get a random subset of widgets for a role.
     */
    public static function get_random( string $role, int $limit = 1 ): array {
        $widgets = self::get_widgets_by_role( $role );
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
    public static function get_by_category( string $category ): array {
        return array_filter(
            self::get_all(),
            static fn( $cfg ) => isset( $cfg['category'] ) && $cfg['category'] === $category
        );
    }

    /**
     * Register default widgets and fire registration hook.
     */
    public static function init(): void {
        $register = [ self::class, 'register_widget' ];
        $register( 'widget_news', [
            'id'          => 'widget_news',
            'label'       => __( 'News', 'artpulse' ),
            'icon'        => 'dashicons-megaphone',
            'description' => __( 'Latest updates from ArtPulse.', 'artpulse' ),
            'callback'    => [ self::class, 'render_widget_news' ],
            'roles'       => [ 'member' ],
            'visibility'  => 'public',
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
        $role = DashboardController::get_role( $user_id );

        echo '<div class="ap-widget-grid">';

        foreach ( self::get_all() as $id => $cfg ) {
            $roles = isset( $cfg['roles'] ) ? (array) $cfg['roles'] : [];
            if ( $roles && ! in_array( $role, $roles, true ) ) {
                continue;
            }

            $cap = $cfg['capability'] ?? '';
            if ( $cap && ! user_can( $user_id, $cap ) ) {
                continue;
            }

            $class = $cfg['class'] ?? '';
            if ( $class && method_exists( $class, 'can_view' ) ) {
                try {
                    if ( ! call_user_func( [ $class, 'can_view' ] ) ) {
                        continue;
                    }
                } catch ( \Throwable $e ) {
                    continue;
                }
            }

            echo '<div class="ap-widget-card">';
            try {
                call_user_func( $cfg['callback'] );
            } catch ( \Throwable $e ) {
                error_log( 'Widget ' . $id . ' failed: ' . $e->getMessage() );
            }
            echo '</div>';
        }

        echo '</div>';
    }
}
