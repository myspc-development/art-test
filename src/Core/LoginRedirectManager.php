<?php
namespace ArtPulse\Core;

use ArtPulse\Helpers\GlobalHelpers;

class LoginRedirectManager {

        public static function register(): void {
                add_filter( 'login_redirect', array( self::class, 'handle' ), 10, 3 );
        }

       public static function handle( $redirect_to, $requested_redirect_to, $user ) {
               if ( is_wp_error( $user ) ) {
                       return $redirect_to;
               }

               if ( current_user_can( 'view_wp_admin' ) || GlobalHelpers::wpAdminAccessEnabled() ) {
                       return $redirect_to;
               }

               $target = self::get_post_login_redirect_url( $user, $requested_redirect_to );
               $target = wp_validate_redirect( $target, $redirect_to );
               $target = esc_url_raw( $target );

               return $target;
       }

       public static function get_post_login_redirect_url( $user, $redirect_to ): string {
               $safe = wp_validate_redirect( $redirect_to, '' );
               if ( $safe ) {
                       return $safe;
               }

               $roles = (array) ( $user->roles ?? array() );
               if ( in_array( 'organization', $roles, true ) ) {
                       return Plugin::get_org_dashboard_url();
               }
               if ( in_array( 'artist', $roles, true ) ) {
                       return Plugin::get_artist_dashboard_url();
               }
               return Plugin::get_user_dashboard_url();
       }
}
