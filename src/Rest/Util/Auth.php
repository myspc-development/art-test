<?php
namespace ArtPulse\Rest\Util;

use WP_Error;

final class Auth {
    /**
     * Returns a permission_callback that enforces login and optional capability.
     */
    public static function require_login_and_cap(?callable $capCheck = null): callable {
        return function() use ($capCheck) {
            if ( ! is_user_logged_in() ) {
                return new WP_Error(
                    'rest_not_logged_in',
                    __( 'Authentication required.', 'artpulse' ),
                    [ 'status' => rest_authorization_required_code() ] // 401
                );
            }
            if ( $capCheck && ! $capCheck() ) {
                return new WP_Error(
                    'rest_forbidden',
                    __( 'You do not have permission.', 'artpulse' ),
                    [ 'status' => 403 ]
                );
            }
            return true;
        };
    }
}
