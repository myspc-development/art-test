<?php
namespace ArtPulse\Rest;

class CurrentUserController {
	public static function register(): void {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	public static function register_routes(): void {
		if ( ! ap_rest_route_registered( ARTPULSE_API_NAMESPACE, '/me' ) ) {
			register_rest_route(
				ARTPULSE_API_NAMESPACE,
				'/me',
                               array(
                                       'methods'             => 'GET',
                                       'callback'            => array( self::class, 'get_current_user' ),
                                       'permission_callback' => function () {
                                               return is_user_logged_in();
                                       },
                               )
                        );
                }
        }

        public static function get_current_user() {
                $user = wp_get_current_user();
                return array(
                        'id'    => $user->ID,
                       'role'  => $user->roles[0] ?? '',
                        'roles' => $user->roles,
                );
        }
}
