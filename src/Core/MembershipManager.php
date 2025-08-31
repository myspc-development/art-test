<?php
namespace ArtPulse\Core;

use Stripe\StripeClient;
use WP_REST_Request;
use WP_Error;
use ArtPulse\Admin\SettingsRegistry;

class MembershipManager {

	public const DEFAULT_LEVELS = array( 'Free', 'Pro', 'Org' );

	public static function getLevels(): array {
		$levels = get_option( 'ap_membership_levels', array() );
		if ( empty( $levels ) ) {
			$levels = self::DEFAULT_LEVELS;
		}
		return array_values( array_unique( $levels ) );
	}

	/**
	 * Register settings tab and fields.
	 */
	public static function register_settings(): void {
		SettingsRegistry::register_tab( 'membership', __( 'Membership', 'artpulse' ) );

		SettingsRegistry::register_field(
			'membership',
			'default_privacy_email',
			array(
				'label'   => __( 'Default Email Privacy', 'artpulse' ),
				'desc'    => __( 'Public or private visibility for new user emails.', 'artpulse' ),
				'type'    => 'select',
				'options' => array(
					'public'  => 'Public',
					'private' => 'Private',
				),
			)
		);

		SettingsRegistry::register_field(
			'membership',
			'default_privacy_location',
			array(
				'label'   => __( 'Default Location Privacy', 'artpulse' ),
				'desc'    => __( 'Public or private visibility for new user locations.', 'artpulse' ),
				'type'    => 'select',
				'options' => array(
					'public'  => 'Public',
					'private' => 'Private',
				),
			)
		);
	}
	/**
	 * Hook all actions.
	 */
	public static function register() {
		self::register_settings();
		// Assign free membership on user registration
		add_action( 'user_register', array( self::class, 'assignFreeMembership' ) );
		// Log registration details
		add_action( 'user_register', array( self::class, 'logRegistration' ) );
		// Set default privacy preferences
		add_action( 'user_register', array( self::class, 'setPrivacyDefaults' ) );

		// Register Stripe webhook endpoint
		add_action( 'rest_api_init', array( self::class, 'registerRestRoutes' ) );

		// Schedule daily expiry checks and notifications
		add_action( 'ap_daily_expiry_check', array( self::class, 'processExpirations' ) );
	}

	/**
	 * Give every new user the Free level.
	 */
	public static function assignFreeMembership( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! user_can( $user, 'artist' ) && ! user_can( $user, 'organization' ) ) {
			if ( user_can( $user, 'administrator' ) ) {
				// Don't override admin privileges when registering
				$user->add_role( 'member' );
			} else {
				$user->set_role( 'member' );
			}
		}

		if ( user_can( $user, 'organization' ) && ! get_user_meta( $user_id, 'ap_pending_organization_id', true ) ) {
			$org_id = wp_insert_post(
				array(
					'post_type'   => 'artpulse_org',
					'post_status' => 'pending',
					'post_title'  => $user->display_name ?: $user->user_login,
					'post_author' => $user_id,
				)
			);
			if ( ! is_wp_error( $org_id ) ) {
				update_user_meta( $user_id, 'ap_pending_organization_id', $org_id );
			}
		}

		if ( user_can( $user, 'artist' ) && ! get_user_meta( $user_id, 'ap_pending_artist_request_id', true ) ) {
			$req_id = wp_insert_post(
				array(
					'post_type'   => 'ap_artist_request',
					'post_status' => 'pending',
					'post_title'  => 'Artist Upgrade: User ' . $user_id,
					'post_author' => $user_id,
				)
			);
			if ( ! is_wp_error( $req_id ) ) {
				update_user_meta( $user_id, 'ap_pending_artist_request_id', $req_id );
			}
		}
		update_user_meta( $user_id, 'ap_membership_level', 'Free' );

		// Optional: send welcome email
		\ArtPulse\Core\EmailService::send(
			$user->user_email,
			__( 'Welcome to ArtPulse – Free Membership', 'artpulse' ),
			__( 'Thanks for joining! You now have Free membership.', 'artpulse' )
		);
	}

	/**
	 * Log registration time and IP address.
	 */
	public static function logRegistration( $user_id ) {
		update_user_meta( $user_id, 'registered_at', current_time( 'mysql' ) );

		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if ( ! empty( $ip ) ) {
			update_user_meta( $user_id, 'registered_ip', sanitize_text_field( $ip ) );
		}
	}

	/**
	 * Set default privacy preferences for new users.
	 */
	public static function setPrivacyDefaults( $user_id ): void {
		$opts  = get_option( 'artpulse_settings', array() );
		$email = $opts['default_privacy_email'] ?? 'public';
		$loc   = $opts['default_privacy_location'] ?? 'public';
		add_user_meta( $user_id, 'ap_privacy_email', $email, true );
		add_user_meta( $user_id, 'ap_privacy_location', $loc, true );
	}

	/**
	 * Expose a public REST endpoint for Stripe webhooks.
	 */
	public static function registerRestRoutes() {
		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/stripe-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'handleStripeWebhook' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'read' ) ) {
						return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
					}
					return true;
				},
			)
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/membership/pause',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'pauseMembership' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'read' ) ) {
						return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
					}
					return true;
				},
			)
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/membership/resume',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'resumeMembership' ),
				'permission_callback' => function () {
					if ( ! current_user_can( 'read' ) ) {
						return new \WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
					}
					return true;
				},
			)
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/membership/levels',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( self::class, 'getLevelsEndpoint' ),
					'permission_callback' => array( self::class, 'checkManageMemberships' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( self::class, 'addLevel' ),
					'permission_callback' => array( self::class, 'checkManageMemberships' ),
				),
			)
		);

		register_rest_route(
			ARTPULSE_API_NAMESPACE,
			'/membership/levels/(?P<level>[\\w-]+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( self::class, 'deleteLevel' ),
				'permission_callback' => array( self::class, 'checkManageMemberships' ),
			)
		);
	}

	/**
	 * Handle incoming Stripe webhook events.
	 */
	public static function handleStripeWebhook( WP_REST_Request $request ) {
		$payload    = $request->get_body();
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
		$settings   = get_option( 'artpulse_settings', array() );
		$secret     = $settings['stripe_webhook_secret'] ?? '';

		try {
			$event = \Stripe\Webhook::constructEvent( $payload, $sig_header, $secret );
		} catch ( \UnexpectedValueException $e ) {
			return new WP_Error( 'invalid_payload', 'Invalid payload', array( 'status' => 400 ) );
		} catch ( \Stripe\Exception\SignatureVerificationException $e ) {
			return new WP_Error( 'invalid_signature', 'Invalid signature', array( 'status' => 400 ) );
		}

		// Stripe client for retrieving full objects if needed
		$stripe = new StripeClient( $settings['stripe_secret'] ?? '' );

		switch ( $event->type ) {

			// Initial checkout session → customer created
			case 'checkout.session.completed':
				$session = $event->data->object;
				$user_id = absint( $session->client_reference_id ?? 0 );
				if ( $user_id ) {
					// record customer ID and set Pro for one month
					update_user_meta( $user_id, 'stripe_customer_id', sanitize_text_field( $session->customer ) );
					update_user_meta( $user_id, 'ap_membership_level', 'Pro' );
					$expiry = strtotime( '+1 month', current_time( 'timestamp' ) );
					update_user_meta( $user_id, 'ap_membership_expires', $expiry );

					$amount   = isset( $session->amount_total ) ? $session->amount_total / 100 : 0;
					$currency = strtoupper( $session->currency ?? '' );
					$renewal  = date_i18n( get_option( 'date_format' ), $expiry );
					$content  = sprintf(
						'Payment received: %s %s. Next renewal on %s.',
						number_format_i18n( $amount, 2 ),
						$currency,
						$renewal
					);
					if ( class_exists( 'ArtPulse\\Community\\NotificationManager' ) ) {
						\ArtPulse\Community\NotificationManager::add(
							$user_id,
							'payment_paid',
							null,
							null,
							$content
						);
					}
				}
				break;

			// Subscription created or renewed
			case 'customer.subscription.created':
			case 'invoice.payment_succeeded':
				$sub    = $event->data->object;
				$custId = $sub->customer;
				// find user by stripe_customer_id
				$user = get_users(
					array(
						'meta_key'   => 'stripe_customer_id',
						'meta_value' => $custId,
						'number'     => 1,
						'fields'     => 'ID',
					)
				);
				if ( ! empty( $user ) ) {
					$user_id = $user[0];
					update_user_meta( $user_id, 'ap_membership_level', 'Pro' );
					// Stripe sends current_period_end timestamp
					$expiry = intval( $sub->current_period_end );
					update_user_meta( $user_id, 'ap_membership_expires', $expiry );

					$amount   = isset( $sub->amount_paid ) ? $sub->amount_paid / 100 : 0;
					$currency = strtoupper( $sub->currency ?? '' );
					$renewal  = date_i18n( get_option( 'date_format' ), $expiry );
					$content  = sprintf(
						'Payment received: %s %s. Next renewal on %s.',
						number_format_i18n( $amount, 2 ),
						$currency,
						$renewal
					);
					if ( class_exists( 'ArtPulse\\Community\\NotificationManager' ) ) {
						\ArtPulse\Community\NotificationManager::add(
							$user_id,
							'payment_paid',
							null,
							null,
							$content
						);
					}
				}
				break;

			case 'payment_intent.succeeded':
				$intent = $event->data->object;
				try {
					$pi = $stripe->paymentIntents->retrieve( $intent->id, array( 'expand' => array( 'charges' ) ) );
				} catch ( \Exception $e ) {
					error_log( 'Stripe: Error retrieving PaymentIntent ' . $intent->id . ' - ' . $e->getMessage() );
					break;
				}

				$charge   = $pi->charges->data[0] ?? null;
				$fraud    = $charge->fraud_details ?? array();
				$warnings = array();

				try {
					$result   = $stripe->radar->earlyFraudWarnings->all( array( 'payment_intent' => $pi->id ) );
					$warnings = $result->data;
				} catch ( \Exception $e ) {
					error_log( 'Stripe: Error retrieving early fraud warnings - ' . $e->getMessage() );
				}

				if ( ! empty( (array) $fraud ) || ! empty( $warnings ) ) {
					$admin_email = get_option( 'admin_email' );
					$message     = "Potential fraud detected for PaymentIntent {$pi->id}.\n";
					if ( ! empty( (array) $fraud ) ) {
						$message .= 'Fraud details: ' . wp_json_encode( $fraud ) . "\n";
					}
					if ( ! empty( $warnings ) ) {
						$ids      = array_map(
							static function ( $w ) {
								return $w->id;
							},
							$warnings
						);
						$message .= 'Early fraud warnings: ' . implode( ', ', $ids ) . "\n";
					}
					\ArtPulse\Core\EmailService::send(
						$admin_email,
						'ArtPulse: Stripe Fraud Alert',
						$message
					);
					error_log( $message );
				}

				break;

			// Subscription cancelled or payment failed → downgrade immediately
			case 'customer.subscription.deleted':
			case 'invoice.payment_failed':
				$obj    = $event->data->object;
				$custId = $obj->customer;
				$user   = get_users(
					array(
						'meta_key'   => 'stripe_customer_id',
						'meta_value' => $custId,
						'number'     => 1,
						'fields'     => 'ID',
					)
				);
				if ( ! empty( $user ) ) {
					$user_id = $user[0];
					// downgrade
					$usr = get_userdata( $user_id );
					if ( user_can( $usr, 'administrator' ) ) {
						// Administrators keep admin capabilities during downgrades
						$usr->add_role( 'subscriber' );
					} else {
						$usr->set_role( 'subscriber' );
					}
					update_user_meta( $user_id, 'ap_membership_level', 'Free' );
					update_user_meta( $user_id, 'ap_membership_expires', current_time( 'timestamp' ) );

					// notify user
					\ArtPulse\Core\EmailService::send(
						$usr->user_email,
						__( 'Your ArtPulse membership has been cancelled', 'artpulse' ),
						__( 'Your subscription has ended or payment failed. You are now on Free membership.', 'artpulse' )
					);

					$amount   = isset( $obj->amount_due ) ? $obj->amount_due / 100 : 0;
					$currency = strtoupper( $obj->currency ?? '' );
					$content  = $amount ?
						sprintf(
							'Payment of %s %s failed and your membership was downgraded.',
							number_format_i18n( $amount, 2 ),
							$currency
						) :
						'Payment failed and your membership was downgraded.';

					if ( class_exists( 'ArtPulse\\Community\\NotificationManager' ) ) {
						\ArtPulse\Community\NotificationManager::add(
							$user_id,
							'payment_failed',
							null,
							null,
							$content
						);
						if ( $event->type === 'customer.subscription.deleted' ) {
							\ArtPulse\Community\NotificationManager::add(
								$user_id,
								'membership_expired',
								null,
								null,
								'Your membership has expired and you have been moved to Free level.'
							);
						}
					}
				}
				break;

			// Add other event types here…

			default:
				// do nothing
				break;
		}

		delete_transient( 'ap_payment_metrics' );
		return \rest_ensure_response( array( 'received' => true ) );
	}

	public static function pauseMembership( WP_REST_Request $request ) {
		$user_id     = get_current_user_id();
		$customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
		$settings    = get_option( 'artpulse_settings', array() );
		$secret      = $settings['stripe_secret'] ?? '';

		if ( ! $customer_id || ! $secret ) {
			return new WP_Error( 'missing_data', 'Subscription not configured', array( 'status' => 400 ) );
		}

		$stripe = new StripeClient( $secret );

		try {
			$subs = $stripe->subscriptions->all(
				array(
					'customer' => $customer_id,
					'status'   => 'active',
					'limit'    => 1,
				)
			);
			if ( empty( $subs->data ) ) {
				return new WP_Error( 'no_subscription', 'No active subscription', array( 'status' => 404 ) );
			}

			$sub = $stripe->subscriptions->update(
				$subs->data[0]->id,
				array( 'pause_collection' => array( 'behavior' => 'void' ) )
			);

			update_user_meta( $user_id, 'ap_membership_paused', 1 );
			update_user_meta( $user_id, 'ap_membership_expires', $sub->current_period_end );
		} catch ( \Exception $e ) {
			return new WP_Error( 'stripe_error', $e->getMessage(), array( 'status' => 500 ) );
		}

		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function resumeMembership( WP_REST_Request $request ) {
		$user_id     = get_current_user_id();
		$customer_id = get_user_meta( $user_id, 'stripe_customer_id', true );
		$settings    = get_option( 'artpulse_settings', array() );
		$secret      = $settings['stripe_secret'] ?? '';

		if ( ! $customer_id || ! $secret ) {
			return new WP_Error( 'missing_data', 'Subscription not configured', array( 'status' => 400 ) );
		}

		$stripe = new StripeClient( $secret );

		try {
			$subs = $stripe->subscriptions->all(
				array(
					'customer' => $customer_id,
					'status'   => 'all',
					'limit'    => 1,
				)
			);
			if ( empty( $subs->data ) ) {
				return new WP_Error( 'no_subscription', 'Subscription not found', array( 'status' => 404 ) );
			}

			$sub = $stripe->subscriptions->update(
				$subs->data[0]->id,
				array( 'pause_collection' => '' )
			);

			update_user_meta( $user_id, 'ap_membership_paused', 0 );
			update_user_meta( $user_id, 'ap_membership_expires', $sub->current_period_end );
		} catch ( \Exception $e ) {
			return new WP_Error( 'stripe_error', $e->getMessage(), array( 'status' => 500 ) );
		}

		return \rest_ensure_response( array( 'success' => true ) );
	}

	public static function checkManageMemberships() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Unauthorized.', 'artpulse' ), array( 'status' => 403 ) );
		}
		return true;
	}

	public static function getLevelsEndpoint() {
		return \rest_ensure_response( self::getLevels() );
	}

	public static function addLevel( WP_REST_Request $req ) {
		$level = sanitize_text_field( $req->get_param( 'level' ) );
		if ( ! $level ) {
			return new WP_Error( 'invalid_level', 'Invalid level', array( 'status' => 400 ) );
		}
		$levels   = self::getLevels();
		$levels[] = $level;
		update_option( 'ap_membership_levels', array_values( array_unique( $levels ) ) );
		return \rest_ensure_response( array( 'levels' => $levels ) );
	}

	public static function handleMembershipLevels( WP_REST_Request $req ) {
		return match ( $req->get_method() ) {
			'POST' => self::addLevel( $req ),
			'GET'  => self::getLevelsEndpoint(),
			default => new WP_Error( 'invalid_method', 'Method not allowed', array( 'status' => 405 ) ),
		};
	}

	public static function deleteLevel( WP_REST_Request $req ) {
		$level  = sanitize_text_field( $req['level'] );
		$levels = array_values( array_diff( self::getLevels(), array( $level ) ) );
		update_option( 'ap_membership_levels', $levels );
		return \rest_ensure_response( array( 'levels' => $levels ) );
	}

	/**
	 * Demote any users whose membership has expired.
	 * Runs daily via cron.
	 */
	public static function processExpirations() {
		$now     = current_time( 'timestamp' );
		$expired = get_users(
			array(
				'meta_key'     => 'ap_membership_expires',
				'meta_value'   => $now,
				'meta_compare' => '<=',
			)
		);

		foreach ( $expired as $user ) {
			if ( user_can( $user, 'administrator' ) ) {
				// Keep admin rights when membership expires
				$user->add_role( 'subscriber' );
			} else {
				$user->set_role( 'subscriber' );
			}
			update_user_meta( $user->ID, 'ap_membership_level', 'Free' );

			// Optionally notify
			\ArtPulse\Core\EmailService::send(
				$user->user_email,
				__( 'Your ArtPulse membership has expired', 'artpulse' ),
				__( 'Your Pro membership has expired and you have been moved to Free level.', 'artpulse' )
			);
		}
	}
}
