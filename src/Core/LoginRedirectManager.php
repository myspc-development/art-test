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

               $safe = wp_validate_redirect( $requested_redirect_to );
               if ( $safe ) {
                       $safe = esc_url_raw( $safe );
                       if ( function_exists( 'wp_safe_redirect' ) ) {
                               wp_safe_redirect( $safe );
                       }
                       return $safe;
               }

               $roles = (array) ( $user->roles ?? array() );
               if ( in_array( 'organization', $roles, true ) ) {
                       $dashboard_url = Plugin::get_org_dashboard_url();
               } elseif ( in_array( 'artist', $roles, true ) ) {
                       $dashboard_url = Plugin::get_artist_dashboard_url();
               } else {
                       $dashboard_url = Plugin::get_user_dashboard_url();
               }

               $dashboard_url = esc_url_raw( $dashboard_url );
               if ( function_exists( 'wp_safe_redirect' ) ) {
                       wp_safe_redirect( $dashboard_url );
               }

               return $dashboard_url;
       }
}
