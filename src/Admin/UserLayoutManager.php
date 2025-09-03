<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardController;
use ArtPulse\Core\DashboardPresets;
use ArtPulse\Dashboard\WidgetGuard;
use ArtPulse\Core\WidgetRegistry;

/**
 * Manage dashboard widget layouts for users and roles.
 */
class UserLayoutManager {

	public const META_KEY     = 'ap_dashboard_layout';
	public const VIS_META_KEY = 'ap_widget_visibility';
	/**
	 * Get a user's widget layout with fallbacks.
	 *
	 * @deprecated Use get_layout_for_user() instead.
	 */
	public static function get_layout( int $user_id ): array {
		return self::get_layout_for_user( $user_id );
	}

	/**
	 * Save a user's widget layout.
	 */
	public static function save_layout( int $user_id, array $layout ): void {
		$valid   = array_keys( DashboardWidgetRegistry::get_all() );
		$ordered = \ArtPulse\Core\LayoutUtils::normalize_layout( $layout, $valid );

		update_user_meta( $user_id, self::META_KEY, $ordered );
	}

	/**
	 * Alias for save_layout for backward compatibility.
	 */
	public static function save_user_layout( int $user_id, array $layout ): void {
		self::save_layout( $user_id, $layout );
	}

	/**
	 * Get the default layout for a role.
	 *
	 * @return array{layout:array<array<string,mixed>>,logs:array<int,string>}
	 */
	public static function get_role_layout( string $role ): array {
		$config = get_option( 'ap_dashboard_widget_config', array() );
		$entry  = $config[ $role ] ?? array();
		$layout = array();

		if ( is_array( $entry ) && isset( $entry['layout'] ) ) {
			$layout = $entry['layout'];
		} elseif ( is_array( $entry ) ) {
			$layout = $entry;
		}

               if ( is_array( $layout ) && ! empty( $layout ) ) {
                       $defs   = DashboardWidgetRegistry::get_definitions();
                       $valid  = array_keys( $defs );
                       $logs   = array();
                       $ordered = \ArtPulse\Core\LayoutUtils::normalize_layout( $layout, $valid, $logs );

                      $final = array();
                      foreach ( $ordered as $item ) {
                               $slug = $item['id']; // preserve original slug for logging
                               $def  = $defs[ $slug ] ?? null;
                               $vis  = $item['visible'] ?? true;

                               if ( ! $def ) {
                                       if ( ! in_array( $slug, $logs, true ) ) {
                                               $logs[] = $slug;
                                       }
                                       WidgetGuard::register_stub_widget( $slug, array(), array() );
                               }

                               $final[] = array(
                                       'id'      => $slug,
                                       'visible' => $vis,
                               );
                       }

			if ( $logs && defined( 'ARTPULSE_TEST_VERBOSE' ) && ARTPULSE_TEST_VERBOSE ) {
				error_log( 'Invalid dashboard widgets for role ' . $role . ': ' . implode( ', ', $logs ) );
			}

			return array(
				'layout' => $final,
				'logs'   => $logs,
			);
		}

		$default_ids = \ArtPulse\Core\DashboardController::get_widgets_for_role( $role );
		$layout      = array_map(
			fn( $id ) => array(
				'id'      => $id,
				'visible' => true,
			),
			$default_ids
		);
               if ( empty( $layout ) ) {
                       if ( 'administrator' === $role ) {
                               // Administrators may intentionally have no widgets. Suppress the
                               // empty dashboard warning and return an empty layout.
                               return array(
                                       'layout' => array(),
                                       'logs'   => array(),
                               );
                       }

                       $stub = 'empty_dashboard';
                       WidgetGuard::register_stub_widget( $stub, array(), array() );
                       error_log( 'Empty dashboard layout resolved for role ' . $role );
                       return array(
                               'layout' => array(
                                       array(
                                               'id'      => $stub,
                                               'visible' => true,
                                       ),
                               ),
                               'logs'   => array( $stub ),
                       );
               }
               return array(
                       'layout' => $layout,
                       'logs'   => array(),
               );
       }

	/**
	 * Save the default layout for a role.
	 */
	public static function save_role_layout( string $role, array $layout ): void {
		$valid   = array_column( DashboardWidgetRegistry::get_definitions(), 'id' );
		$ordered = \ArtPulse\Core\LayoutUtils::normalize_layout( $layout, $valid );

		$config   = get_option( 'ap_dashboard_widget_config', array() );
		$role_key = sanitize_key( $role );
		$entry    = $config[ $role_key ] ?? array();
		$style    = array();
		if ( is_array( $entry ) && isset( $entry['style'] ) ) {
			$style = $entry['style'];
		}

		$config[ $role_key ] = array( 'layout' => $ordered );
		if ( $style ) {
			$config[ $role_key ]['style'] = $style;
		}

                update_option( 'ap_dashboard_widget_config', $config );

                DashboardPresets::resetCache();
        }

	public static function export_layout( string $role ): string {
		return json_encode( self::get_role_layout( $role )['layout'], JSON_PRETTY_PRINT );
	}

	public static function import_layout( string $role, string $json ): bool {
		$decoded = json_decode( $json, true );
		if ( is_array( $decoded ) ) {
			self::save_role_layout( $role, $decoded );
			return true;
		}
		return false;
	}

	/**
	 * Get style configuration for a role.
	 */
	public static function get_role_style( string $role ): array {
		$config = get_option( 'ap_dashboard_widget_config', array() );
		$entry  = $config[ $role ] ?? array();
		if ( is_array( $entry ) && isset( $entry['style'] ) && is_array( $entry['style'] ) ) {
			return $entry['style'];
		}
		return array();
	}

	/**
	 * Save style configuration for a role.
	 */
	public static function save_role_style( string $role, array $style ): void {
		$sanitized = array();
		foreach ( $style as $k => $v ) {
			$key               = sanitize_key( $k );
			$val               = is_string( $v ) ? sanitize_text_field( $v ) : $v;
			$sanitized[ $key ] = $val;
		}

		$config   = get_option( 'ap_dashboard_widget_config', array() );
		$role_key = sanitize_key( $role );
		$entry    = $config[ $role_key ] ?? array();

		if ( ! is_array( $entry ) ) {
			$entry = array( 'layout' => is_array( $entry ) ? $entry : array() );
		}

		$entry['style']      = $sanitized;
		$config[ $role_key ] = $entry;

		update_option( 'ap_dashboard_widget_config', $config );
	}

	public static function reset_layout_for_role( string $role ): void {
		$config   = get_option( 'ap_dashboard_widget_config', array() );
		$role_key = sanitize_key( $role );
		if ( isset( $config[ $role_key ] ) ) {
			unset( $config[ $role_key ] );
			update_option( 'ap_dashboard_widget_config', $config );
		}
	}

        /**
         * Remove a user's saved dashboard layout and visibility.
         */
        public static function reset_user_layout( int $user_id ): void {
                delete_user_meta( $user_id, self::META_KEY );
                delete_user_meta( $user_id, self::VIS_META_KEY );
        }

        /**
         * Retrieve the raw dashboard layout for a user.
         */
        public static function get_user_layout( int $user_id ): array {
                $layout = get_user_meta( $user_id, self::META_KEY, true );
                return is_array( $layout ) ? $layout : array();
        }

       /**
        * Normalize WordPress roles to plugin roles.
        *
        * @param string[] $roles Raw WP roles.
        * @return string[] Normalized plugin roles.
        */
       private static function normalize_roles( array $roles ): array {
               $normalized = array();
               foreach ( $roles as $role ) {
                       $role = strtolower( (string) $role );
                       switch ( $role ) {
                               case 'administrator':
                               case 'organization':
                                       $role = 'organization';
                                       break;
                               case 'subscriber':
                               case 'contributor':
                               case 'author':
                               case 'editor':
                               case 'member':
                                       $role = 'member';
                                       break;
                               case 'artist':
                               case 'shared':
                                       // leave as is.
                                       break;
                               default:
                                       continue 2;
                       }
                       if ( ! in_array( $role, $normalized, true ) ) {
                               $normalized[] = $role;
                       }
               }
               return $normalized;
       }

       /**
        * Merge layouts for multiple roles preserving order and de-duping.
        *
        * @param string[] $roles Roles to merge.
        * @return array<int,array{id:string,visible:bool}>
        */
       private static function merge_role_layouts( array $roles ): array {
               $roles     = self::normalize_roles( $roles );
               $merged    = array();
               $seen_ids  = array();
               $valid_ids = array_column( DashboardWidgetRegistry::get_definitions(), 'id' );
               
               foreach ( $roles as $role ) {
                       $result = self::get_role_layout( $role );
                       foreach ( $result['layout'] as $item ) {
                               $id  = DashboardWidgetRegistry::canon_slug( (string) ( $item['id'] ?? '' ) );
                               $vis = isset( $item['visible'] ) ? (bool) $item['visible'] : true;
                               if ( ! $id || isset( $seen_ids[ $id ] ) || ! in_array( $id, $valid_ids, true ) ) {
                                       continue;
                               }
                               $seen_ids[ $id ] = true;
                               $merged[]        = array(
                                       'id'      => $id,
                                       'visible' => $vis,
                               );
                       }
               }

               return $merged;
       }

	/**
	 * Determine a user's dashboard layout with fallbacks.
	 */
        public static function get_layout_for_user( int $user_id ): array {
               $layout = self::get_user_layout( $user_id );
               if ( ! empty( $layout ) ) {
                       $sanitized = array();
                       foreach ( $layout as $item ) {
                               if ( is_array( $item ) && isset( $item['id'] ) ) {
                                       $id  = WidgetRegistry::normalize_slug( (string) $item['id'] );
                                       $vis = isset( $item['visible'] ) ? (bool) $item['visible'] : true;
                               } else {
                                       $id  = WidgetRegistry::normalize_slug( (string) $item );
                                       $vis = true;
                               }
                               if ( $id === '' ) {
                                       continue;
                               }
                               $sanitized[] = array(
                                       'id'      => $id,
                                       'visible' => $vis,
                               );
                       }

                       $valid   = array_keys( DashboardWidgetRegistry::get_all() );
                       $ordered = \ArtPulse\Core\LayoutUtils::normalize_layout( $sanitized, $valid );

                       $final = array();
                       $seen  = array();
                       foreach ( $ordered as $item ) {
                               $id = $item['id'];
                               if ( ! in_array( $id, $valid, true ) || isset( $seen[ $id ] ) ) {
                                       continue;
                               }
                               $seen[ $id ] = true;
                               $final[]     = $item;
                       }

                       if ( ! empty( $final ) ) {
                               return $final;
                       }
               }

               $user  = get_userdata( $user_id );
               $roles = $user && ! empty( $user->roles ) ? (array) $user->roles : array( 'subscriber' );
               $roles = self::normalize_roles( $roles );
               if ( empty( $roles ) ) {
                       $roles = array( 'member' );
               }
               if ( count( $roles ) > 1 ) {
                       $primary = $roles[0];
                       $others  = array_slice( $roles, 1 );
                       $merged  = self::merge_role_layouts( array_merge( array( $primary ), array( 'shared' ), $others ) );
                       if ( ! empty( $merged ) ) {
                               return $merged;
                       }
               }

               $role   = $roles[0] ?? 'member';
               $result = self::get_role_layout( $role );
               $layout = $result['layout'];
               if ( ! empty( $layout ) ) {
                       return $layout;
               }

               $all = DashboardWidgetRegistry::get_all();
               return array_map(
                       fn( $id ) => array(
                               'id'      => $id,
                               'visible' => true,
                       ),
                       array_keys( $all )
               );
        }

	/**
	 * Get a user's primary role.
	 */
	public static function get_primary_role( int $user_id ): string {
		$user = get_userdata( $user_id );
		return $user && ! empty( $user->roles ) ? $user->roles[0] : 'subscriber';
	}
}
