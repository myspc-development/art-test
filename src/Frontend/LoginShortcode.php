<?php
namespace ArtPulse\Frontend;

/**
 * Shortcode that outputs login and registration forms.
 */
class LoginShortcode {

	private const NOTICE_KEY = 'ap_register_notices';
	/**
	 * Registers hooks and the `ap_login` shortcode.
	 */
	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_login', 'Login', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ap_do_login', array( self::class, 'ajax_login' ) );
		add_action( 'wp_ajax_nopriv_ap_do_login', array( self::class, 'ajax_login' ) );
		add_action( 'wp_ajax_ap_do_register', array( self::class, 'ajax_register' ) );
		add_action( 'wp_ajax_nopriv_ap_do_register', array( self::class, 'ajax_register' ) );
		add_action( 'init', array( self::class, 'handle_form' ) );
	}

	public static function enqueue_scripts(): void {
		if ( is_user_logged_in() ) {
			return;
		}
		wp_enqueue_script( 'ap-auth-js' );
	}

	public static function render(): string {
		if ( is_user_logged_in() ) {
			return '<p>' . esc_html__( 'You are already logged in.', 'artpulse' ) . '</p>';
		}

		ob_start();
		?>
		<div class="ap-login-forms">
			<div id="ap-login-message" class="ap-form-messages" role="status" aria-live="polite"></div>
                       <form id="ap-login-form" class="ap-form-container">
                               <?php wp_nonce_field( 'ap_login_nonce', 'nonce' ); ?>
                               <p>
                                       <label class="ap-form-label" for="ap_login_username"><?php esc_html_e( 'Username or Email', 'artpulse' ); ?></label>
                                       <input class="ap-input" id="ap_login_username" type="text" name="username" placeholder="<?php esc_attr_e( 'Username or Email', 'artpulse' ); ?>" autocomplete="username" required />
                               </p>
                               <p>
                                       <label class="ap-form-label" for="ap_login_password"><?php esc_html_e( 'Password', 'artpulse' ); ?></label>
                                       <input class="ap-input" id="ap_login_password" type="password" name="password" placeholder="<?php esc_attr_e( 'Password', 'artpulse' ); ?>" autocomplete="current-password" required />
                               </p>
                               <p>
                                       <label class="ap-form-label" for="ap_login_remember">
                                               <input class="ap-input" id="ap_login_remember" type="checkbox" name="remember" />
                                               <?php esc_html_e( 'Remember me', 'artpulse' ); ?>
                                       </label>
                               </p>
                               <p>
                                       <button class="ap-form-button nectar-button" type="submit"><?php esc_html_e( 'Login', 'artpulse' ); ?></button>
                               </p>
                       </form>

			<?php echo \ArtPulse\Integration\OAuthManager::render_buttons(); ?>

			<hr />

			<?php
			$template_path = plugin_dir_path( __FILE__ ) . '../../templates/partials/registration-form.php';
			if ( file_exists( $template_path ) ) {
				include $template_path;
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

        public static function ajax_login(): void {
               check_ajax_referer( 'ap_login_nonce', 'nonce' );

               $username_raw = $_POST['username'] ?? '';
               $password     = $_POST['password'] ?? '';

               $invalid = array();
               if ( '' === $username_raw ) {
                       $invalid[] = 'username';
               }
               if ( '' === $password ) {
                       $invalid[] = 'password';
               }
               if ( $invalid ) {
                       wp_send_json_error(
                               array(
                                       'code'    => 'VALIDATION',
                                       'message' => __( 'Username or email and password are required.', 'artpulse' ),
                                       'invalid' => $invalid,
                               ),
                               400
                       );
               }

               $identifier = is_email( $username_raw ) ? sanitize_email( $username_raw ) : sanitize_user( $username_raw );
               $remember   = ! empty( $_POST['remember'] );

               $raw_ip   = $_SERVER['REMOTE_ADDR'] ?? '';
               $san_ip   = sanitize_text_field( $raw_ip );
               $parts    = explode( ',', $san_ip );
               $ip       = filter_var( trim( $parts[0] ?? '' ), FILTER_VALIDATE_IP );
               if ( false === $ip ) {
                       $ip = '';
               }
               $ip       = wp_privacy_anonymize_ip( $ip );
               $key      = 'ap_login_fail_' . md5( $ip . '|' . $identifier );
               $attempts = (int) get_transient( $key );

               $max_attempts    = 5;
               $lockout_minutes = 5;

               if ( $attempts >= $max_attempts ) {
                       wp_send_json_error(
                               array(
                                       'code'    => 'RATE_LIMIT',
                                       'message' => __( 'Too many failed login attempts. Please try again later.', 'artpulse' ),
                               ),
                               429
                       );
               }

               $creds = array(
                       'user_login'    => $identifier,
                       'user_password' => $password,
                       'remember'      => $remember,
               );

               $user = wp_signon( $creds, false );

               if ( is_wp_error( $user ) ) {
                       set_transient( $key, $attempts + 1, MINUTE_IN_SECONDS * $lockout_minutes );
                       wp_send_json_error(
                               array(
                                       'code'    => 'INVALID_CREDENTIALS',
                                       'message' => __( 'Invalid username/email or password.', 'artpulse' ),
                               ),
                               403
                       );
               }

               delete_transient( $key );

               $opts = get_option( 'artpulse_settings', array() );
               if ( ! empty( $opts['enforce_two_factor'] ) && ! get_user_meta( $user->ID, 'two_factor_enabled', true ) ) {
                       wp_clear_auth_cookie();
                       wp_send_json_error(
                               array(
                                       'code'    => 'TWO_FACTOR_REQUIRED',
                                       'message' => __( 'Two-factor authentication is required.', 'artpulse' ),
                               ),
                               403
                       );
               }

               $target = \ArtPulse\Core\LoginRedirectManager::get_post_login_redirect_url( $user, '' );

               wp_send_json_success(
                       array(
                               'message'      => __( 'Signed in successfully.', 'artpulse' ),
                               'dashboardUrl' => $target,
                       )
               );
        }

	public static function ajax_register(): void {
		check_ajax_referer( 'ap_login_nonce', 'nonce' );

		if ( ! apply_filters( 'ap_registration_allowed', true ) ) {
			wp_send_json_error( array( 'message' => __( 'Registration is currently disabled.', 'artpulse' ) ) );
		}

		$username       = sanitize_user( $_POST['username'] ?? '' );
		$email          = sanitize_email( $_POST['email'] ?? '' );
		$password       = $_POST['password'] ?? '';
		$confirm        = $_POST['password_confirm'] ?? '';
		$display_name   = sanitize_text_field( $_POST['display_name'] ?? '' );
		$bio            = sanitize_textarea_field( $_POST['description'] ?? '' );
		$requested_role = sanitize_key( $_POST['role'] ?? 'member' );
		$allowed_roles  = array( 'member' );
		if ( current_user_can( 'promote_users' ) ) {
			$allowed_roles = array_merge( $allowed_roles, array( 'artist', 'organization' ) );
		}
		if ( ! in_array( $requested_role, $allowed_roles, true ) ) {
			if ( in_array( $requested_role, array( 'artist', 'organization' ), true ) ) {
				wp_send_json_error( array( 'message' => __( 'Registration as artist or organization requires approval.', 'artpulse' ) ) );
			}
			$role = 'member';
		} else {
			$role = $requested_role;
		}
		$components = array();
		if ( ! empty( $_POST['address_components'] ) ) {
			$decoded = json_decode( stripslashes( $_POST['address_components'] ), true );
			if ( is_array( $decoded ) ) {
				$components = $decoded;
			}
		}
		$country = isset( $components['country'] ) ? sanitize_text_field( $components['country'] ) : '';
		$state   = isset( $components['state'] ) ? sanitize_text_field( $components['state'] ) : '';
		$city    = isset( $components['city'] ) ? sanitize_text_field( $components['city'] ) : '';
		$suburb  = isset( $components['suburb'] ) ? sanitize_text_field( $components['suburb'] ) : '';
		$street  = isset( $components['street'] ) ? sanitize_text_field( $components['street'] ) : '';

		$opts               = get_option( 'artpulse_settings', array() );
		$default_email_priv = $opts['default_privacy_email'] ?? 'public';
		$default_loc_priv   = $opts['default_privacy_location'] ?? 'public';
		$email_privacy      = sanitize_text_field( $_POST['ap_privacy_email'] ?? $default_email_priv );
		$location_privacy   = sanitize_text_field( $_POST['ap_privacy_location'] ?? $default_loc_priv );
		if ( ! in_array( $email_privacy, array( 'public', 'private' ), true ) ) {
			$email_privacy = $default_email_priv;
		}
		if ( ! in_array( $location_privacy, array( 'public', 'private' ), true ) ) {
			$location_privacy = $default_loc_priv;
		}

		$min_length = (int) apply_filters( 'ap_min_password_length', 8 );
		if (
			strlen( $password ) < $min_length ||
			! preg_match( '/[A-Za-z]/', $password ) ||
			! preg_match( '/\d/', $password )
		) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %d: minimum password length */
						__( 'Password must be at least %d characters long and include both letters and numbers.', 'artpulse' ),
						$min_length
					),
				)
			);
		}

		if ( $confirm !== '' && $confirm !== $password ) {
			wp_send_json_error(
				array(
					'message' => __( 'Passwords do not match.', 'artpulse' ),
				)
			);
		}

		$result = wp_create_user( $username, $password, $email );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Assign the selected role
		wp_update_user(
			array(
				'ID'   => $result,
				'role' => $role,
			)
		);

		// Auto login the new user
		wp_set_current_user( $result );
		wp_set_auth_cookie( $result );

		if ( $display_name ) {
			wp_update_user(
				array(
					'ID'           => $result,
					'display_name' => $display_name,
				)
			);
		}
		if ( $bio !== '' ) {
			update_user_meta( $result, 'description', $bio );
		}
		if ( $country !== '' ) {
			update_user_meta( $result, 'ap_country', $country );
		}
		if ( $state !== '' ) {
			update_user_meta( $result, 'ap_state', $state );
		}
		if ( $city !== '' ) {
			update_user_meta( $result, 'ap_city', $city );
		}
		if ( $suburb !== '' ) {
			update_user_meta( $result, 'ap_suburb', $suburb );
		}
		if ( $street !== '' ) {
			update_user_meta( $result, 'ap_street', $street );
		}
		update_user_meta( $result, 'ap_privacy_email', $email_privacy );
		update_user_meta( $result, 'ap_privacy_location', $location_privacy );

		wp_send_json_success(
			array(
				'message' => __( 'Registration successful. Redirecting to your dashboard…', 'artpulse' ),
			)
		);
	}

	public static function handle_form(): void {
		if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset( $_GET['ap-register'] ) ) {
			return;
		}

		if ( ! isset( $_POST['ap_register_nonce'] ) || ! wp_verify_nonce( $_POST['ap_register_nonce'], 'ap_register_form' ) ) {
			self::add_notice( __( 'Security check failed.', 'artpulse' ) );
			self::maybe_redirect();
			return;
		}

		if ( ! apply_filters( 'ap_registration_allowed', true ) ) {
			self::add_notice( __( 'Registration is currently disabled.', 'artpulse' ) );
			self::maybe_redirect();
			return;
		}

		$username       = sanitize_user( $_POST['username'] ?? '' );
		$email          = sanitize_email( $_POST['email'] ?? '' );
		$password       = $_POST['password'] ?? '';
		$confirm        = $_POST['password_confirm'] ?? '';
		$display_name   = sanitize_text_field( $_POST['display_name'] ?? '' );
		$bio            = sanitize_textarea_field( $_POST['description'] ?? '' );
		$requested_role = sanitize_key( $_POST['role'] ?? 'member' );
		$allowed_roles  = array( 'member' );
		if ( current_user_can( 'promote_users' ) ) {
			$allowed_roles = array_merge( $allowed_roles, array( 'artist', 'organization' ) );
		}
		if ( ! in_array( $requested_role, $allowed_roles, true ) ) {
			if ( in_array( $requested_role, array( 'artist', 'organization' ), true ) ) {
				self::add_notice( __( 'Registration as artist or organization requires approval.', 'artpulse' ) );
				self::maybe_redirect();
				return;
			}
			$role = 'member';
		} else {
			$role = $requested_role;
		}

		$components = array();
		if ( ! empty( $_POST['address_components'] ) ) {
			$decoded = json_decode( stripslashes( $_POST['address_components'] ), true );
			if ( is_array( $decoded ) ) {
				$components = $decoded;
			}
		}

		$country = isset( $components['country'] ) ? sanitize_text_field( $components['country'] ) : '';
		$state   = isset( $components['state'] ) ? sanitize_text_field( $components['state'] ) : '';
		$city    = isset( $components['city'] ) ? sanitize_text_field( $components['city'] ) : '';

		$opts               = get_option( 'artpulse_settings', array() );
		$default_email_priv = $opts['default_privacy_email'] ?? 'public';
		$default_loc_priv   = $opts['default_privacy_location'] ?? 'public';
		$email_privacy      = sanitize_text_field( $_POST['ap_privacy_email'] ?? $default_email_priv );
		$location_privacy   = sanitize_text_field( $_POST['ap_privacy_location'] ?? $default_loc_priv );
		if ( ! in_array( $email_privacy, array( 'public', 'private' ), true ) ) {
			$email_privacy = $default_email_priv;
		}
		if ( ! in_array( $location_privacy, array( 'public', 'private' ), true ) ) {
			$location_privacy = $default_loc_priv;
		}

		$min_length = (int) apply_filters( 'ap_min_password_length', 8 );
		if (
			strlen( $password ) < $min_length ||
			! preg_match( '/[A-Za-z]/', $password ) ||
			! preg_match( '/\d/', $password )
		) {
			self::add_notice(
				sprintf(
				/* translators: %d: minimum password length */
					__( 'Password must be at least %d characters long and include both letters and numbers.', 'artpulse' ),
					$min_length
				)
			);
			self::maybe_redirect();
			return;
		}

		if ( $confirm !== '' && $confirm !== $password ) {
			self::add_notice( __( 'Passwords do not match.', 'artpulse' ) );
			self::maybe_redirect();
			return;
		}

		$result = wp_create_user( $username, $password, $email );
		if ( is_wp_error( $result ) ) {
			self::add_notice( $result->get_error_message() );
			self::maybe_redirect();
			return;
		}

		wp_update_user(
			array(
				'ID'   => $result,
				'role' => $role,
			)
		);

		wp_set_current_user( $result );
		wp_set_auth_cookie( $result );

		if ( $display_name ) {
			wp_update_user(
				array(
					'ID'           => $result,
					'display_name' => $display_name,
				)
			);
		}
		if ( $bio !== '' ) {
			update_user_meta( $result, 'description', $bio );
		}
		if ( $country !== '' ) {
			update_user_meta( $result, 'ap_country', $country );
		}
		if ( $state !== '' ) {
			update_user_meta( $result, 'ap_state', $state );
		}
		if ( $city !== '' ) {
			update_user_meta( $result, 'ap_city', $city );
		}
		if ( $suburb !== '' ) {
			update_user_meta( $result, 'ap_suburb', $suburb );
		}
		if ( $street !== '' ) {
			update_user_meta( $result, 'ap_street', $street );
		}
		update_user_meta( $result, 'ap_privacy_email', $email_privacy );
		update_user_meta( $result, 'ap_privacy_location', $location_privacy );

		$target = \ArtPulse\Core\Plugin::get_user_dashboard_url();
		if ( 'organization' === $role ) {
			$target = \ArtPulse\Core\Plugin::get_org_dashboard_url();
		} elseif ( 'artist' === $role ) {
			$target = \ArtPulse\Core\Plugin::get_artist_dashboard_url();
		}

		wp_safe_redirect( $target );
		exit;
	}

	public static function print_notices(): void {
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
			return;
		}

		$notices = get_transient( self::NOTICE_KEY );
		if ( $notices ) {
			foreach ( $notices as $notice ) {
				$type    = esc_attr( $notice['type'] );
				$message = esc_html( $notice['message'] );
				echo "<div class='notice {$type}'>{$message}</div>";
			}
			delete_transient( self::NOTICE_KEY );
		}
	}

	private static function add_notice( string $message, string $type = 'error' ): void {
		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $message, $type );
			return;
		}

		$notices   = get_transient( self::NOTICE_KEY ) ?: array();
		$notices[] = array(
			'message' => $message,
			'type'    => $type,
		);
		set_transient( self::NOTICE_KEY, $notices, defined( 'MINUTE_IN_SECONDS' ) ? MINUTE_IN_SECONDS : 60 );
	}

	private static function maybe_redirect(): void {
		if ( function_exists( 'wp_safe_redirect' ) && function_exists( 'wp_get_referer' ) ) {
			$target = wp_get_referer();
			if ( ! $target ) {
				$target = \ArtPulse\Core\Plugin::get_user_dashboard_url();
			}
			wp_safe_redirect( $target );
			exit;
		}
	}
}
