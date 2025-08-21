<?php
namespace ArtPulse\Rest\Util;

use WP_Error;
use WP_REST_Request;

/**
 * Generate a permission callback enforcing login and optional capability check.
 */
function require_login_and_cap(callable $capCheck = null) {
    return function (WP_REST_Request $request = null) use ($capCheck) {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_not_logged_in',
                __('Authentication required.'),
                ['status' => rest_authorization_required_code()]
            );
        }
        if ($capCheck && !$capCheck($request)) {
            return new WP_Error('rest_forbidden', __('You do not have permission.'), ['status' => 403]);
        }
        return true;
    };
}
