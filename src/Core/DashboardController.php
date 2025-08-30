<?php
namespace ArtPulse\Core;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Frontend\ArtistDashboardShortcode;
use ArtPulse\Frontend\OrganizationDashboardShortcode;
use ArtPulse\Dashboard\WidgetGuard;
use ArtPulse\Core\RoleResolver;
use ArtPulse\Core\LayoutUtils;
use ArtPulse\Core\WidgetRegistryLoader;
use ArtPulse\Core\WidgetRegistry;
use ArtPulse\Support\WidgetIds;
use WP;
use WP_Query;

if ( ! defined( 'ARTPULSE_SKIP_TEMPLATE_COPY' ) ) {
	define( 'ARTPULSE_SKIP_TEMPLATE_COPY', true );
}

class DashboardController {

	/** @var string[] */
	private const ALLOWED_ROLES = array( 'member', 'artist', 'organization' );

	/** Expose current role for template if you prefer property over query var */
	private ?string $current_role = null;

	/**
	 * Default widgets available to each role.
	 *
	 * Widget IDs must match those registered via {@see DashboardWidgetRegistry}.
	 *
	 * @var array<string,string[]>
	 */
	private static array $role_widgets = array(
		// Default widgets for newly created members
		'member'       => array(
                        'widget_news',
                        'widget_membership',
                        'widget_upgrade',
                        'widget_account_tools',
                        'widget_recommended_for_you',
                        'widget_my_rsvps',
                        'widget_favorites',
                        'widget_local_events',
                        'widget_my_follows',
                        'widget_notifications',
                        'widget_messages',
                        'widget_dashboard_feedback',
                        'widget_cat_fact',
                ),
                // Artist dashboard starter widgets
                'artist'       => array(
                        'widget_artist_feed_publisher',
                        'widget_artist_audience_insights',
                        'widget_artist_spotlight',
                        'widget_artist_revenue_summary',
                        'widget_my_events',
                        'widget_messages',
                        'widget_notifications',
                        'widget_dashboard_feedback',
                        'widget_cat_fact',
                ),
                // Organization admin widgets
                // Synced with widget manifest. Guard below will warn on drift.
                'organization' => array(
                        'widget_org_event_overview',
                        'widget_artpulse_analytics_widget',
                        'widget_org_ticket_insights',
                        'widget_my_events',
                        'widget_org_team_roster',
                        'widget_audience_crm',
                        'widget_org_broadcast_box',
                        'widget_org_approval_center',
                        'widget_webhooks',
                        'widget_support_history',
                ),
               // WordPress administrators don't have a default dashboard layout.
               // Use an explicit empty configuration so callers can distinguish
               // between an intentionally empty set and an unknown role.
               'administrator' => array(),
        );

        /** @var bool */
        private static bool $defaults_checked = false;

        /**
         * Optional preset overrides set at runtime.
         *
         * @var array<string,array>
         */
        private static array $preset_overrides = array();

	public static function init(): void {
		add_filter( 'query_vars', array( __CLASS__, 'registerQueryVars' ) );
		add_action( 'parse_query', array( __CLASS__, 'resolveRoleIntoQuery' ) );
		add_filter( 'template_include', array( __CLASS__, 'interceptTemplate' ), 9 );
	}


        /**
         * Inject preset overrides.
         */
        public static function set_presets( array $presets ): void {
                self::$preset_overrides = $presets;
        }

        /**
         * Raw layout presets keyed by unique identifier.
         *
         * These presets are not filtered for widget registration or role/capability
         * access. Call {@see get_default_presets()} for a sanitized version.
         *
         * @return array<string,array{title:string,role:string,layout:array<int,array{id:string}>}>
         */
        public static function get_raw_presets(): array {
                if ( ! empty( self::$preset_overrides ) ) {
                        return self::$preset_overrides;
                }

                return array(
                                'member_default'   => array(
                                        'title'  => 'Member Default',
                                        'role'   => 'member',
                                        'layout' => array(
                                                array( 'id' => 'widget_news' ),
                                                array( 'id' => 'widget_favorites' ),
                                                array( 'id' => 'widget_events' ),
                                                array( 'id' => 'instagram_widget' ),
                                        ),
                                ),
                                'artist_default'   => array(
                                        'title'  => 'Artist Default',
                                        'role'   => 'artist',
                                        'layout' => array_values(
                                                array_filter(
                                                        array(
                                                                defined( 'AP_DEV_MODE' ) && AP_DEV_MODE ? array( 'id' => 'activity_feed' ) : null,
                                                                array( 'id' => 'artist_inbox_preview' ),
                                                                array( 'id' => 'artist_revenue_summary' ),
                                                                array( 'id' => 'artist_spotlight' ),
                                                                array( 'id' => 'widget_favorites' ),
                                                                defined( 'AP_DEV_MODE' ) && AP_DEV_MODE ? array( 'id' => 'qa_checklist' ) : null,
                                                        )
                                                )
                                        ),
                                ),
                                // New sample layouts that can be applied from the dashboard UI
                                'new_member_intro' => array(
                                        'title'  => 'New Member Intro',
                                        'role'   => 'member',
                                        'layout' => self::load_preset_layout( 'member', 'discovery' ),
                                ),
                                'artist_tools'     => array(
                                        'title'  => 'Artist Tools',
                                        'role'   => 'artist',
                                        'layout' => self::load_preset_layout( 'artist', 'tools' ),
                                ),
                                'org_admin_start'  => array(
                                        'title'  => 'Organization Admin Start',
                                        'role'   => 'organization',
                                        'layout' => self::load_preset_layout( 'organization', 'admin' ),
                                ),
                );
        }

        /**
         * Default layout presets keyed by unique identifier.
         *
         * Preset layouts are filtered so only widgets the specified role can access
         * are returned. Unregistered widgets, widgets limited to other roles and
         * widgets requiring capabilities the role lacks are automatically removed.
         *
         * @return array<string,array{title:string,role:string,layout:array<int,array{id:string}>}>
         */
        public static function get_default_presets(): array {
                $presets = self::get_raw_presets();

                // Remove widgets the role cannot access.
                foreach ( $presets as $key => $preset ) {
                        $layout = self::filter_accessible_layout(
                                $preset['layout'],
                                $preset['role']
                        );
                        if ( empty( $layout ) ) {
                                $stub = sanitize_key( $key . '_placeholder' );
                                WidgetGuard::register_stub_widget( $stub, array(), array( 'roles' => array( $preset['role'] ) ) );
                                if (
                                        defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG &&
                                        function_exists( 'is_user_logged_in' ) && is_user_logged_in()
                                ) {
                                        error_log( "[Dashboard Preset] {$key} for role {$preset['role']} missing widgets; registered stub {$stub}" );
                                }
                                $layout = array( array( 'id' => $stub ) );
                        }
                        $presets[ $key ]['layout'] = $layout;
                }

                return $presets;
        }

	/**
	 * Filter a preset layout so it only contains widgets the role can access.
	 */
        private static function filter_accessible_layout( array $layout, string $role ): array {
                $filtered = array();

                foreach ( $layout as $entry ) {
                        $id = $entry['id'] ?? '';
                        if ( ! $id ) {
                                continue;
                        }

                        $result = WidgetAccessValidator::validate( $id, $role, $entry );
                        if ( ! $result['allowed'] ) {
                                continue;
                        }

                        $filtered[] = array(
                                'id'      => $id,
                                'visible' => $entry['visible'] ?? true,
                        );
                }

                return $filtered;
        }

	/**
	 * Verify that default widget IDs are registered and log a warning once.
	 */
	private static function verify_default_widgets(): void {
		if ( self::$defaults_checked ) {
			return;
		}

		self::$defaults_checked = true;

		$missing = array();
                foreach ( self::$role_widgets as $ids ) {
                        foreach ( $ids as $id ) {
                                $id = WidgetIds::canonicalize( $id );
                                if ( ! DashboardWidgetRegistry::exists( $id ) ) {
                                        $missing[] = $id;
                                }
                        }
                }

		if ( $missing ) {
			foreach ( array_unique( $missing ) as $id ) {
				WidgetGuard::register_stub_widget( $id, array(), array() );
				if (
					defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG &&
					function_exists( 'is_user_logged_in' ) && is_user_logged_in()
				) {
					error_log( "[DashboardController] Registered stub widget {$id}" );
				}
			}
			trigger_error(
				'Unregistered dashboard widget defaults: ' . implode( ', ', array_unique( $missing ) ),
				E_USER_WARNING
			);
		}
	}

	/**
	 * Get the widgets assigned to a role.
	 */
	public static function get_widgets_for_role( string $role ): array {
		self::verify_default_widgets();

		if ( isset( self::$role_widgets[ $role ] ) ) {
			$widgets = self::$role_widgets[ $role ];
		} else {
			$widgets = array();
		}

		$widgets = array_map( array( WidgetRegistry::class, 'normalize_slug' ), $widgets );
		$known   = WidgetRegistry::get_canonical_ids();
		if ( ! empty( $known ) ) {
			$widgets = array_values( array_unique( array_intersect( $widgets, $known ) ) );
		}

                $valid = array();
                foreach ( $widgets as $id ) {
                        $config = DashboardWidgetRegistry::get( $id );
                        if ( ! $config ) {
                                trigger_error( 'Dashboard widget not registered: ' . $id, E_USER_WARNING );
                                continue;
                        }

                        $roles = isset( $config['roles'] ) ? (array) $config['roles'] : array();
                        if ( $roles && ! in_array( $role, $roles, true ) ) {
                                continue;
                        }

                        $cap = $config['capability'] ?? '';
                        if (
                                $cap &&
                                ( \function_exists( __NAMESPACE__ . '\get_role' ) || \function_exists( '\\get_role' ) )
                        ) {
                                $role_obj = \function_exists( __NAMESPACE__ . '\get_role' )
                                        ? \call_user_func( __NAMESPACE__ . '\get_role', $role )
                                        : \get_role( $role );
                                if ( $role_obj && method_exists( $role_obj, 'has_cap' ) && ! $role_obj->has_cap( $cap ) ) {
                                        continue;
                                }
                        }

                        $class = $config['class'] ?? '';
                        if ( $class && method_exists( $class, 'can_view' ) ) {
                                try {
                                        if ( ! call_user_func( array( $class, 'can_view' ), 0 ) ) {
                                                continue;
                                        }
                                } catch ( \Throwable $e ) {
                                        continue;
                                }
                        }

                        $valid[] = $id;
                }

                return $valid;
        }

	/**
	 * Determine the dashboard layout for a user. Checks user overrides then
	 * falls back to the default widgets for their role.
	 */
	public static function get_user_dashboard_layout( int $user_id ): array {
               if (
                       isset( $_GET['ap_preview_user'], $_GET['ap_preview_nonce'] ) &&
                       current_user_can( 'manage_options' ) &&
                       wp_verify_nonce( sanitize_key( $_GET['ap_preview_nonce'] ), 'ap_preview' )
               ) {
                       $preview = (int) $_GET['ap_preview_user'];
                       if ( $preview > 0 ) {
                               $user_id = $preview;
                       }
               }

		$role = self::get_role( $user_id );

		// Ensure all widgets are registered before deriving the layout.
		if ( empty( DashboardWidgetRegistry::get_all() ) && function_exists( 'plugin_dir_path' ) ) {
			WidgetRegistryLoader::register_widgets();
		}

		// Load the raw layout from user meta, options, or defaults
		$custom = get_user_meta( $user_id, 'ap_dashboard_layout', true );
		$layout = array();

		if ( ! empty( $custom ) && is_array( $custom ) ) {
			$layout = $custom;
		} else {
			$layouts = get_option( 'ap_dashboard_widget_config', array() );
			if ( ! empty( $layouts[ $role ] ) && is_array( $layouts[ $role ] ) ) {
				$layout = $layouts[ $role ];
			} else {
				$layout = array_map(
					fn( $id ) => array( 'id' => $id ),
					self::get_widgets_for_role( $role )
				);
			}
		}

		$layout = array_map(
			static function ( $entry ) {
				if ( is_array( $entry ) && isset( $entry['id'] ) ) {
					$entry['id'] = WidgetRegistry::normalize_slug( sanitize_key( $entry['id'] ) );
				}
				return $entry;
			},
			$layout
		);

		$all       = DashboardWidgetRegistry::get_all();
		$valid_ids = array_keys( $all );
		// Normalize the layout before filtering by role or capabilities.
		$layout = LayoutUtils::normalize_layout( $layout, $valid_ids );
		$layout = array_values(
			array_filter(
				$layout,
				static fn( $w ) => in_array( $w['id'], $valid_ids, true )
			)
		);

                // Filter out any widgets the user cannot access
                $filtered = array_values(
                        array_filter(
                                $layout,
                                static function ( $w ) use ( $user_id ) {
                                        $id = $w['id'] ?? null;
                                        return $id && DashboardWidgetRegistry::user_can_see( $id, $user_id );
                                }
                        )
                );

		if ( empty( $filtered ) ) {
			WidgetGuard::register_stub_widget( 'empty_dashboard', array( 'title' => 'Dashboard Placeholder' ), array( 'roles' => array( $role ) ) );
			$filtered = array(
				array(
					'id'      => 'empty_dashboard',
					'visible' => true,
				),
			);
			/**
			 * Fires when a user's dashboard layout resolves to an empty set.
			 *
			 * Plugins may use this to display a notice or offer to load a preset
			 * layout for the current role.
			 */
			do_action( 'ap_dashboard_empty_layout', $user_id, $role );
		}

		return $filtered;
	}

	/**
	 * Reset a user's dashboard layout if corrupted or contains widgets not allowed
	 * for their role.
	 *
	 * @return bool True when the layout was reset.
	 */
	public static function reset_user_dashboard_layout( int $user_id ): bool {
		$role        = self::get_role( $user_id );
		$default_ids = self::get_widgets_for_role( $role );

		if ( empty( $default_ids ) ) {
			delete_user_meta( $user_id, 'ap_dashboard_layout' );
			return false;
		}

		$current     = get_user_meta( $user_id, 'ap_dashboard_layout', true );
		$needs_reset = false;

		if ( ! is_array( $current ) || empty( $current ) ) {
			$needs_reset = true;
		} else {
			$ids = array();
			foreach ( $current as $item ) {
				if ( is_array( $item ) && isset( $item['id'] ) ) {
					$ids[] = WidgetRegistry::normalize_slug( sanitize_key( $item['id'] ) );
				} elseif ( is_string( $item ) ) {
					$ids[] = WidgetRegistry::normalize_slug( sanitize_key( $item ) );
				}
			}
			$allowed = array_keys( DashboardWidgetRegistry::get_widgets( $role, $user_id ) );
			$missing = array_diff( $default_ids, $ids );
			$unauth  = array_diff( $ids, $allowed );
			if ( count( $missing ) >= floor( count( $default_ids ) / 2 ) || ! empty( $unauth ) ) {
				$needs_reset = true;
			}
		}

		if ( $needs_reset ) {
			$layout = array_map( fn( $id ) => array( 'id' => $id ), $default_ids );
			update_user_meta( $user_id, 'ap_dashboard_layout', $layout );
			return true;
		}

		return false;
	}

	/**
	 * Load a preset layout file for a role.
	 */
	public static function load_preset_layout( string $role, string $preset ): array {
		$file = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . "data/presets/{$role}-{$preset}.json";
		if ( file_exists( $file ) ) {
			$json   = file_get_contents( $file );
			$layout = json_decode( $json, true );

			if ( ! is_array( $layout ) ) {
				return array();
			}

			$clean = array();
			foreach ( $layout as $entry ) {
				if ( ! is_array( $entry ) || ! isset( $entry['id'] ) ) {
					continue;
				}

				$id = WidgetRegistry::normalize_slug( sanitize_key( $entry['id'] ) );

				if ( ! WidgetRegistry::exists( $id ) && ! DashboardWidgetRegistry::exists( $id ) ) {
					if (
						defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG &&
						function_exists( 'is_user_logged_in' ) && is_user_logged_in()
					) {
						error_log( "[Dashboard Preset] Widget {$id} not registered" );
					}
					continue;
				}

				$entry['id'] = $id;
				$clean[]     = $entry;
			}

			return $clean;
		}
		return array();
	}
	/**
	 * Render the dashboard for a specific user.
	 *
	 * @param int $user_id User ID to render the dashboard for.
	 *
	 * @return string Dashboard HTML for the user.
	 */
	public static function render_for_user( int $user_id ): string {
		if ( ! $user_id ) {
			return '';
		}

		ob_start();
		DashboardWidgetRegistry::render_for_role( (int) $user_id );
		return ob_get_clean();
	}

	/**
	 * Render the current user's dashboard.
	 *
	 * This helper loads the correct dashboard for the logged-in user and
	 * returns the generated HTML. When no user is logged in a small
	 * login prompt is returned instead of triggering errors.
	 */
	public static function render(): string {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'Please log in to view your dashboard.', 'artpulse' ) . '</p>';
		}

		return self::render_for_user( get_current_user_id() );
	}

	/**
	 * Helper alias for get_role().
	 */
	public static function get_user_role( $user_id = null ): string {
		$user_id = $user_id ?: get_current_user_id();
		return self::get_role( $user_id );
	}

       public static function get_role( $user_id ): string {
               if (
                       function_exists( '\ap_get_effective_role' )
                       && ( $user_id === 0 || $user_id === get_current_user_id() )
               ) {
                       $role = \ap_get_effective_role();
               } else {
                       $role = RoleResolver::resolve( $user_id );
               }

               if ( empty( $role ) ) {
                       $role = 'member';
               }

               return sanitize_key( (string) $role );
       }

	/**
	 * Retrieve custom dashboard widget posts visible to the user's role.
	 */
	public static function get_custom_widgets_for_user( int $user_id ): array {
		$role = self::get_role( $user_id );

		$args = array(
			'post_type'      => 'dashboard_widget',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value_num',
			'meta_key'       => 'widget_order',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => 'visible_to_roles',
					'value'   => $role,
					'compare' => 'LIKE',
				),
			),
		);

		return get_posts( $args );
	}

	public static function on_activate(): void {
		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
		}
		self::maybe_copy_template();
	}

	public static function maybe_copy_template(): void {
		if ( ARTPULSE_SKIP_TEMPLATE_COPY ) {
			return;
		}
		$dest_dir = get_stylesheet_directory();
		$src      = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/single-artpulse_event.php';
		$dest     = trailingslashit( $dest_dir ) . 'single-artpulse_event.php';
		if ( ! is_readable( $src ) || ! is_writable( $dest_dir ) ) {
			return;
		}
		if ( ! file_exists( $dest ) ) {
			if ( ! @copy( $src, $dest ) ) {
				if ( defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG && function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
					error_log( 'ArtPulse: failed to copy template single-artpulse_event.php' );
				}
			}
		}
	}

	/**
	 * Back-compat shim that proxies to {@see interceptTemplate()}.
	 *
	 * @param string $template
	 * @return string
	 */
	public static function template_include( string $template ): string {
		return self::interceptTemplate( $template );
	}

	/**
	 * Filter template_include to force our dashboard template when requested.
	 */
	public static function interceptTemplate( string $template ): string {
		$is_query = ( function_exists( '\get_query_var' ) && \get_query_var( 'ap_dashboard' ) === '1' )
			|| ( isset( $_GET['ap_dashboard'] ) && $_GET['ap_dashboard'] === '1' );
		$is_page  = function_exists( '\is_page' ) && \is_page( 'dashboard' );
		if ( ( $is_query || $is_page ) && function_exists( '\is_user_logged_in' ) && \is_user_logged_in() &&
			function_exists( '\current_user_can' ) && \current_user_can( 'view_artpulse_dashboard' ) ) {

			$base = defined( 'ARTPULSE_PLUGIN_DIR' ) ? ARTPULSE_PLUGIN_DIR : \plugin_dir_path( ARTPULSE_PLUGIN_FILE );
			$tpl  = rtrim( $base, '/\\' ) . '/templates/simple-dashboard.php';
			if ( file_exists( $tpl ) ) {
				if ( defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG ) {
					error_log( '🔥 template_include resolved: ' . $tpl );
				}
				return $tpl;
			}
		}
		return $template;
	}

	public static function registerQueryVars( array $vars ): array {
		$vars[] = 'ap_dashboard';
		$vars[] = 'role';
		$vars[] = 'ap_role';
		return $vars;
	}

	public static function resolveRoleIntoQuery( \WP_Query $q ): void {
		$req  = isset( $_GET['role'] ) ? sanitize_key( (string) $_GET['role'] ) : (string) $q->get( 'role' );
		$role = in_array( $req, self::ALLOWED_ROLES, true ) ? $req : 'member';
		set_query_var( 'ap_role', $role );

		if ( defined( 'AP_VERBOSE_DEBUG' ) && AP_VERBOSE_DEBUG && function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			add_action(
				'send_headers',
				static function () use ( $role ) {
					header( 'X-AP-Resolved-Role: ' . $role );
				}
			);
		}
	}
}

if ( function_exists( 'add_action' ) ) {
	add_action( 'init', array( DashboardController::class, 'init' ) );

	add_action(
		'init',
		static function () {
			add_rewrite_rule( '^dashboard/?$', 'index.php?ap_dashboard=1', 'top' );
		},
		5
	);

	register_activation_hook( ARTPULSE_PLUGIN_FILE, array( DashboardController::class, 'on_activate' ) );
}
