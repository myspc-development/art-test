<?php
namespace ArtPulse\Core;

/**
 * Restrict access to wp-admin and hide the admin toolbar for non-admin users.
 */
class AdminAccessManager {

	/**
	 * Register hooks.
	 */
	public static function register(): void {
		add_filter( 'show_admin_bar', array( self::class, 'maybe_hide_admin_bar' ) );
		add_action( 'admin_init', array( self::class, 'maybe_redirect_admin' ) );
	}

	/**
	 * Hide the admin bar for users without manage_options capability.
	 */
	public static function maybe_hide_admin_bar( bool $show ): bool {
		if (
			current_user_can( 'manage_options' ) ||
			current_user_can( 'view_wp_admin' ) ||
			ap_wp_admin_access_enabled()
		) {
			return $show;
		}

		return false;
	}

	/**
	 * Redirect non-admin users away from wp-admin.
	 */
	public static function maybe_redirect_admin(): void {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'dashboard-role' ) {
			return;
		}
		if (
			wp_doing_ajax() ||
			! is_user_logged_in() ||
			current_user_can( 'manage_options' ) ||
			current_user_can( 'view_wp_admin' ) ||
			ap_wp_admin_access_enabled()
		) {
			return;
		}

               $dashboard_url = Plugin::get_user_dashboard_url();
               wp_safe_redirect( $dashboard_url );
               exit;
       }
}
