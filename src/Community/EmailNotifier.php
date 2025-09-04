<?php
namespace ArtPulse\Community;

class EmailNotifier {
	/**
	 * Notification types that will trigger an email.
	 *
	 * @var array<string>
	 */
	public static $email_types = array(
		'link_request_sent',
		'link_request_approved',
		'link_request_denied',
		'follower',
		'favorite',
		'favorite_added',
		'comment',
		'membership_upgrade',
		'membership_downgrade',
		'membership_expired',
		'payment_paid',
		'payment_failed',
		'payment_refunded',
		'rsvp_received',
		'event_approved',
		'event_rejected',
	);

	/**
	 * Trigger an email if the notification type is configured for email.
	 */
	public static function maybe_send( $user_id, $type, $object_id = null, $related_id = null, $content = '' ) {
		if ( ! in_array( $type, self::$email_types, true ) ) {
			return;
		}

		$prefs = get_user_meta( $user_id, 'ap_notification_prefs', true );
		if ( is_array( $prefs ) && array_key_exists( 'email', $prefs ) && ! $prefs['email'] ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}

		$subject = self::generate_subject( $type, $content );
		$body    = self::generate_body( $user, $content );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		\ArtPulse\Core\EmailService::send(
			$user->user_email,
			$subject,
			$body,
			$headers
		);
	}

	/**
	 * Send an email using the configured delivery method.
	 */
	public static function send( $to, string $subject, string $message, array $headers = array() ): bool {
		$options      = get_option( 'artpulse_settings', array() );
		$method       = $options['email_method'] ?? 'wp_mail';
		$from_name    = $options['email_from_name'] ?? '';
		$from_address = $options['email_from_address'] ?? '';

		$from_cb = null;
		$name_cb = null;
		if ( $from_address ) {
			$from_cb = static function () use ( $from_address ) {
				return $from_address;
			};
			add_filter( 'wp_mail_from', $from_cb );
		}
		if ( $from_name ) {
			$name_cb = static function () use ( $from_name ) {
				return $from_name;
			};
			add_filter( 'wp_mail_from_name', $name_cb );
		}

		try {
			switch ( $method ) {
				case 'mailgun':
					return self::send_mailgun(
						$to,
						$subject,
						$message,
						$options['mailgun_api_key'] ?? '',
						$options['mailgun_domain'] ?? '',
						$from_name,
						$from_address
					);
				case 'sendgrid':
					return self::send_sendgrid( $to, $subject, $message, $options['sendgrid_api_key'] ?? '', $from_name, $from_address );
                                default:
                                        list( $to, $subject, $message, $headers ) = apply_filters(
                                                'wp_mail',
                                                array( $to, $subject, $message, $headers )
                                        );
                                        return wp_mail( $to, $subject, $message, $headers );
                        }
		} finally {
			if ( $from_cb ) {
				remove_filter( 'wp_mail_from', $from_cb );
			}
			if ( $name_cb ) {
				remove_filter( 'wp_mail_from_name', $name_cb );
			}
		}
	}

	private static function send_mailgun(
		$to,
		string $subject,
		string $message,
		string $api_key,
		string $domain,
		string $from_name,
		string $from_address
	): bool {
		if ( ! $api_key || ! $domain ) {
			return false;
		}
		$from = $from_address;
                if ( $from_name ) {
                        $from = sprintf( '%1$s <%2$s>', $from_name, $from_address );
                }
		$response = wp_remote_post(
			"https://api.mailgun.net/v3/{$domain}/messages",
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'api:' . $api_key ),
				),
				'body'    => array(
					'from'    => $from,
					'to'      => $to,
					'subject' => $subject,
					'text'    => $message,
				),
			)
		);
		return ! is_wp_error( $response );
	}

	private static function send_sendgrid( $to, string $subject, string $message, string $api_key, string $from_name, string $from_address ): bool {
		if ( ! $api_key ) {
			return false;
		}
		$payload  = array(
			'personalizations' => array(
				array(
					'to' => array( array( 'email' => $to ) ),
				),
			),
			'from'             => array(
				'email' => $from_address,
				'name'  => $from_name,
			),
			'subject'          => $subject,
			'content'          => array(
				array(
					'type'  => 'text/plain',
					'value' => $message,
				),
			),
		);
		$response = wp_remote_post(
			'https://api.sendgrid.com/v3/mail/send',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);
		return ! is_wp_error( $response );
	}

	/**
	 * Create email subject from notification type.
	 */
	private static function generate_subject( $type, $fallback ) {
		$map = array(
			'link_request_sent'     => 'New Profile Link Request',
			'link_request_approved' => 'Your Profile Link Was Approved',
			'link_request_denied'   => 'Your Profile Link Was Denied',
			'follower'              => 'You Have a New Follower',
			'favorite'              => 'Your Work Was Favorited',
			'favorite_added'        => 'Favorite Saved',
			'comment'               => 'New Comment Received',
			'membership_upgrade'    => 'Membership Upgraded',
			'membership_downgrade'  => 'Membership Downgraded',
			'membership_expired'    => 'Membership Expired',
			'payment_paid'          => 'Payment Received',
			'payment_failed'        => 'Payment Failed',
			'payment_refunded'      => 'Payment Refunded',
			'rsvp_received'         => 'New RSVP Received',
		);
		return $map[ $type ] ?? wp_strip_all_tags( $fallback );
	}

	/**
	 * Generate simple HTML email body.
	 */
	private static function generate_body( $user, $content ) {
		return sprintf(
			__( 'Hi %s,', 'artpulse' ) . '<p>%s</p>' . __( 'Thanks,<br/>ArtPulse Team', 'artpulse' ),
			esc_html( $user->display_name ),
			nl2br( esc_html( $content ) )
		);
	}
}
