<?php
namespace ArtPulse\Rest\Util;

use WP_Error;

final class Auth {
    /**
     * Returns a permission_callback suitable for register_rest_route().
     * - 401 when unauthenticated
     * - 403 when authenticated but lacks capability
     */
    public static function require_login_and_cap(callable $capCheck): callable {
        return static function () use ($capCheck) {
            if ( ! is_user_logged_in() ) {
                return new WP_Error('rest_auth_required', __('Authentication required.', 'artpulse'), ['status' => 401]);
            }
            $ok = (bool) call_user_func($capCheck);
            if ( ! $ok ) {
                return new WP_Error('rest_forbidden', __('You do not have permission.', 'artpulse'), ['status' => 403]);
            }
            return true;
        };
    }
}
