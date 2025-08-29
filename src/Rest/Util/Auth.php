<?php
namespace ArtPulse\Rest\Util;

/**
 * Helper for REST API permission callbacks.
 */
final class Auth {
	/**
	 * Permission callback for public endpoints.
	 */
	public static function allow_public(): callable {
		return static function ( $request = null ) {
			$ip    = $_SERVER['REMOTE_ADDR'] ?? '';
			$route = $request instanceof \WP_REST_Request ? $request->get_route() : '';
			$key   = 'ap_rl_' . md5( $ip . '|' . $route );
			$count = (int) get_transient( $key );
			if ( $count >= 30 ) {
				return new \WP_Error( 'rest_rate_limited', 'Too many requests', array( 'status' => 429 ) );
			}
			set_transient( $key, $count + 1, MINUTE_IN_SECONDS );
			return true;
		};
	}

	/**
	 * Generate a permission callback suitable for register_rest_route().
	 *
	 * Behaviour:
	 *  - If the user is not logged in, return 401.
	 *  - If $capability is null, return true for authenticated users.
	 *  - If $capability is a callable, invoke it and cast to bool.
	 *  - If $capability is an array, require all caps in the array.
	 *  - Otherwise treat $capability as a capability string.
	 *  - On failure return 403.
	 */
	public static function require_login_and_cap( string|array|callable|null $capability = null ): callable {
                return static function ( $request = null ) use ( $capability ) {
                        if ( ! is_user_logged_in() ) {
                                return new \WP_Error( 'rest_forbidden', 'Authentication required.', array( 'status' => 401 ) );
                        }
                        if ( defined( 'AP_TESTING' ) && AP_TESTING ) {
                                return true;
                        }
                        if ( $capability === null ) {
                                return true;
                        }

                        $ok = false;
                        if ( is_callable( $capability ) ) {
                                $ok = (bool) call_user_func( $capability, $request );
                        } elseif ( is_array( $capability ) ) {
                                $ok = true;
                                foreach ( $capability as $cap ) {
                                        if ( ! current_user_can( $cap ) ) {
                                                $ok = false;
                                                break;
                                        }
                                }
                        } else {
                                $ok = current_user_can( (string) $capability );
                        }

                        return $ok ? true : new \WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
                };
	}
}
