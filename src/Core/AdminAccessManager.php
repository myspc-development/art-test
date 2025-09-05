<?php
namespace ArtPulse\Core;

use ArtPulse\Helpers\GlobalHelpers;

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
                        GlobalHelpers::wpAdminAccessEnabled()
		) {
			return $show;
		}

		return false;
	}

	/**
	 * Redirect non-admin users away from wp-admin.
	 */
	public static function maybe_redirect_admin(): void {
               if ( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) === 'dashboard-role' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Sanitized via sanitize_key().
                       return;
               }
               if (
                       wp_doing_ajax() ||
                       ! is_user_logged_in() ||
                       current_user_can( 'manage_options' ) ||
                       current_user_can( 'view_wp_admin' ) ||
                       GlobalHelpers::wpAdminAccessEnabled()
               ) {
                       return;
               }

               $user = wp_get_current_user();
               $dashboard_url = LoginRedirectManager::get_post_login_redirect_url( $user, '' );
               wp_safe_redirect( $dashboard_url );
               exit;
       }
}
