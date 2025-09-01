<?php
namespace ArtPulse\Core;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardPresets;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\DashboardWidgetTools;

/**
 * Central manager for dashboard widgets and layouts.
 */
class DashboardWidgetManager {

	public static function register(): void {
		// Initialize the registry on `init` so widgets can hook in early.
		add_action( 'init', array( self::class, 'bootstrap' ) );

		// Allow external code to include widget files before registration.
		add_action( 'artpulse_register_dashboard_widget', array( self::class, 'load_widget_files' ), 0 );

		// AJAX handlers for saving layouts.
		add_action( 'wp_ajax_ap_save_user_layout', array( self::class, 'ajax_save_user_layout' ) );
		add_action( 'wp_ajax_ap_save_role_layout', array( self::class, 'ajax_save_role_layout' ) );

		// Filters to retrieve layouts.
		add_filter( 'artpulse_dashboard_user_layout', array( self::class, 'getUserLayout' ), 10, 2 );
		add_filter( 'artpulse_dashboard_role_layout', array( self::class, 'getRoleLayout' ), 10, 1 );

                // Ensure newly created users receive a default dashboard layout.
                add_action( 'user_register', array( self::class, 'assign_default_layout' ), 10, 2 );
	}

	/**
	 * Bootstraps the widget registry and fires the registration hook.
	 */
	public static function bootstrap(): void {
		DashboardWidgetRegistry::init();
	}

	/**
	 * Include widget files from the manifest.
	 */
	public static function load_widget_files(): void {
		$manifest = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'widget-manifest.json';
		if ( ! file_exists( $manifest ) ) {
			return;
		}

		$widgets = json_decode( file_get_contents( $manifest ), true );
		if ( ! is_array( $widgets ) ) {
			return;
		}

		foreach ( $widgets as $info ) {
			if ( ( $info['status'] ?? '' ) !== 'registered' || empty( $info['file'] ) ) {
				continue;
			}

			$path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $info['file'];
			if ( pathinfo( $path, PATHINFO_EXTENSION ) !== 'php' || ! file_exists( $path ) ) {
				continue;
			}

			include_once $path;
		}
	}

	/**
	 * Handle the user layout save AJAX request.
	 */
	public static function ajax_save_user_layout(): void {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}
		check_ajax_referer( 'ap_save_user_layout', 'nonce' );

		$layout = null;
		if ( isset( $_POST['layout'] ) ) {
			$raw    = is_string( $_POST['layout'] ) ? stripslashes( $_POST['layout'] ) : $_POST['layout'];
			$layout = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
		} else {
			$body = json_decode( file_get_contents( 'php://input' ), true );
			if ( is_array( $body ) ) {
				$layout = $body['layout'] ?? null;
			}
		}

		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => 'Invalid data' ) );
		}

		$uid = get_current_user_id();
		if ( $uid ) {
			self::saveUserLayout( $uid, $layout );
			wp_send_json_success( array( 'message' => 'Layout saved' ) );
		}

		wp_send_json_error( array( 'message' => 'Invalid user' ) );
	}

	/**
	 * Handle the role layout save AJAX request.
	 */
	public static function ajax_save_role_layout(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}
		check_ajax_referer( 'ap_save_role_layout', 'nonce' );

		$role = sanitize_key( $_POST['role'] ?? '' );
		if ( ! $role ) {
			wp_send_json_error( array( 'message' => 'Invalid role' ) );
		}

		$layout = $_POST['layout'] ?? array();
		if ( is_string( $layout ) ) {
			$layout = json_decode( $layout, true );
		}
		$layout = is_array( $layout ) ? $layout : array();

		self::saveRoleLayout( $role, $layout );
		wp_send_json_success( array( 'saved' => true ) );
	}

	public static function registerWidget(
		string $id,
		string $label,
		string $icon,
		string $description,
		callable $callback,
		array $options = array()
	): void {
		DashboardWidgetRegistry::register( $id, $label, $icon, $description, $callback, $options );
	}

	public static function getWidgetDefinitions( bool $include_schema = false ): array {
		return DashboardWidgetRegistry::get_definitions( $include_schema );
	}

	public static function saveUserLayout( int $user_id, array $layout ): void {
		UserLayoutManager::save_user_layout( $user_id, $layout );
	}

	public static function getUserLayout( int $user_id ): array {
		return UserLayoutManager::get_layout_for_user( $user_id );
	}

	public static function saveRoleLayout( string $role, array $layout ): void {
		UserLayoutManager::save_role_layout( $role, $layout );
	}

	public static function getRoleLayout( string $role ): array {
		return UserLayoutManager::get_role_layout( $role );
	}

	public static function exportRoleLayout( string $role ): string {
		return UserLayoutManager::export_layout( $role );
	}

	public static function importRoleLayout( string $role, string $json ): bool {
		return UserLayoutManager::import_layout( $role, $json );
	}

	public static function resetUserLayout( int $user_id ): void {
		UserLayoutManager::reset_user_layout( $user_id );
	}

	/**
	 * Assign a default dashboard layout to newly created users.
	 *
	 * If the user has no saved layout, populate it using the role's default
	 * configuration. Falls back to a minimal layout containing the core
	 * `my-events` widget when no role preset is available.
	 */
       public static function assign_default_layout( int $user_id, $user = null ): void {
               if ( defined( 'AP_TEST_MODE' ) && AP_TEST_MODE ) {
                       return;
               }

               $current = get_user_meta( $user_id, UserLayoutManager::META_KEY, true );
               if ( is_array( $current ) && ! empty( $current ) ) {
                       return;
               }

               // Determine the user's primary role.
               $role = '';
               if ( $user instanceof \WP_User && ! empty( $user->roles ) ) {
                       $role = sanitize_key( (string) $user->roles[0] );
               }
               if ( ! $role ) {
                       $role = UserLayoutManager::get_primary_role( $user_id );
               }

               // Compute the canonical default layout for the role.
               $ids    = DashboardPresets::forRole( $role );
               $layout = array();
               foreach ( $ids as $id ) {
                       $cid = DashboardWidgetRegistry::canon_slug( $id );
                       if ( $cid ) {
                               $layout[] = array(
                                       'id'      => $cid,
                                       'visible' => true,
                               );
                       }
               }

               if ( empty( $layout ) ) {
                       $layout[] = array(
                               'id'      => 'my-events',
                               'visible' => true,
                       );
               }

               update_user_meta( $user_id, UserLayoutManager::META_KEY, $layout );
       }

	public static function renderPreview( string $role ): void {
		DashboardWidgetTools::render_role_dashboard_preview( $role );
	}
}
