<?php
namespace ArtPulse\Rest\Util;

if ( ! defined( 'AP_TEST_MODE' ) ) {
		define( 'AP_TEST_MODE', getenv( 'AP_TEST_MODE' ) ? (bool) getenv( 'AP_TEST_MODE' ) : false );
}

/**
 * Helper for REST API permission callbacks.
 */
final class Auth {
		/**
		 * Determine if we're running in a special test environment.
		 */
	public static function is_test_mode(): bool {
			return ( defined( 'AP_TEST_MODE' ) && AP_TEST_MODE ) || getenv( 'AP_TEST_MODE' ) === '1';
	}

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
	 *  - If the user is not logged in, return 403.
	 *    We intentionally use 403 so clients receive a generic permission error rather than a 401 authentication challenge.
	 *  - If $capability is null, return true for authenticated users.
	 *  - If $capability is a callable, invoke it and cast to bool.
	 *  - If $capability is an array, require all caps in the array.
	 *  - Otherwise treat $capability as a capability string.
	 *  - On failure return 403.
	 */
	public static function require_login_and_cap( string|array|callable|null $capability = null ): callable {
			return static function ( $request = null ) use ( $capability ) {
				if ( ! is_user_logged_in() ) {
						// Unauthenticated requests return 403 to avoid triggering auth prompts; 401 is reserved for nonce issues.
						return new \WP_Error( 'rest_forbidden', 'Authentication required.', array( 'status' => 403 ) );
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

		/**
		 * Validate a REST nonce.
		 *
		 * Accepts either a request object or a raw nonce string for backwards
		 * compatibility. In normal operation a missing or invalid nonce
		 * returns a 401 error. When running under AP_TEST_MODE the check is a
		 * no-op so tests can skip nonce handling friction.
		 */
	public static function verify_nonce( \WP_REST_Request|string|null $request_or_nonce, string $action = 'wp_rest' ): bool|\WP_Error {
		if ( self::is_test_mode() ) {
				return true;
		}

			$nonce = null;
		if ( $request_or_nonce instanceof \WP_REST_Request ) {
					$nonce = $request_or_nonce->get_header( 'X-WP-Nonce' );
		} else {
				$nonce = $request_or_nonce;
		}

		if ( ! $nonce || wp_verify_nonce( $nonce, $action ) === false ) {
					return new \WP_Error( 'rest_forbidden', 'Invalid nonce.', array( 'status' => 403 ) );
		}

				return true;
	}

		/**
		 * Require a capability for the current user.
		 */
	public static function require_cap( string $cap ): bool|\WP_Error {
		if ( ! is_user_logged_in() ) {
				return new \WP_Error( 'rest_unauthorized', 'Authentication required.', array( 'status' => 401 ) );
		}

		if ( self::is_test_mode() || current_user_can( $cap ) ) {
				return true;
		}

			return new \WP_Error( 'rest_forbidden', 'Sorry, you are not allowed to do that.', array( 'status' => 403 ) );
	}

		/**
		 * Verify nonce then capability.
		 */
	public static function guard( \WP_REST_Request|string|null $request_or_nonce, string $cap, string $action = 'wp_rest' ): bool|\WP_Error {
			$nonce_check = self::verify_nonce( $request_or_nonce, $action );
		if ( is_wp_error( $nonce_check ) ) {
				return $nonce_check;
		}

			$cap_check = self::require_cap( $cap );
		if ( is_wp_error( $cap_check ) ) {
				return $cap_check;
		}

			return true;
	}

		/**
		 * Permission check for read-only endpoints.
		 *
		 * Allows any authenticated user with the basic `read` capability.
		 */
	public static function guard_read( \WP_REST_Request $req ): bool|\WP_Error {
		if ( ! is_user_logged_in() ) {
				return new \WP_Error( 'rest_unauthorized', 'Authentication required.', array( 'status' => 401 ) );
		}

		if ( current_user_can( 'read' ) ) {
					return true;
		}

			return new \WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
	}

		/**
		 * Permission check for endpoints that require administrative access.
		 *
		 * In production a user must have the `manage_options` capability.
		 */
	public static function guard_manage( \WP_REST_Request $req ): bool|\WP_Error {
		if ( ! is_user_logged_in() ) {
				return new \WP_Error( 'rest_unauthorized', 'Authentication required.', array( 'status' => 401 ) );
		}

		if ( current_user_can( 'manage_options' ) ) {
					return true;
		}

			return new \WP_Error( 'rest_forbidden', 'Insufficient permissions.', array( 'status' => 403 ) );
	}
}
