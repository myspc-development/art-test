<?php
namespace ArtPulse\Core;

class LoginRedirectManager {

	public static function register(): void {
		add_filter( 'login_redirect', array( self::class, 'handle' ), 10, 3 );
	}

	public static function handle( $redirect_to, $requested_redirect_to, $user ) {
		if ( is_wp_error( $user ) ) {
			return $redirect_to;
		}

		if ( current_user_can( 'view_wp_admin' ) || ap_wp_admin_access_enabled() ) {
			return $redirect_to;
		}

               $roles = (array) ( $user->roles ?? array() );
               if ( in_array( 'organization', $roles, true ) ) {
                       $dashboard_url = Plugin::get_org_dashboard_url();
               } elseif ( in_array( 'artist', $roles, true ) ) {
                       $dashboard_url = Plugin::get_artist_dashboard_url();
               } else {
                       $dashboard_url = Plugin::get_user_dashboard_url();
               }

               return $dashboard_url;
       }
}
