<?php
namespace ArtPulse\Community;

class NotificationHooks {
	/**
	 * Register all action/event hooks.
	 */
	public static function register() {
		// 🔔 Notify post authors of new approved comments
		add_action( 'comment_post', array( self::class, 'notify_on_comment' ), 10, 3 );

		// 🔔 Membership changes (upgrade/downgrade/expired)
		add_action( 'ap_membership_level_changed', array( self::class, 'notify_on_membership_change' ), 10, 4 );

		// 🔔 Membership payment events
		add_action( 'ap_membership_payment', array( self::class, 'notify_on_payment' ), 10, 4 );

		// 🔔 RSVP added to an event
		add_action( 'ap_event_rsvp_added', array( self::class, 'notify_on_rsvp' ), 10, 2 );

		// 🔔 Event status transitions
		add_action( 'transition_post_status', array( self::class, 'notify_on_event_status' ), 10, 3 );
	}

	/**
	 * Notify post author of a new comment.
	 */
	public static function notify_on_comment( $comment_ID, $comment_approved, $commentdata ) {
		if ( $comment_approved != 1 ) {
			return;
		}

		$post = get_post( $commentdata['comment_post_ID'] );
		if ( ! $post ) {
			return;
		}

		$author_id    = $post->post_author;
		$commenter_id = intval( $commentdata['user_id'] );

		if ( $author_id && $author_id !== $commenter_id ) {
			NotificationManager::add(
				$author_id,
				'comment',
				$post->ID,
				$commenter_id,
                                sprintf( esc_html__( 'New comment on your post "%1$s".', 'artpulse' ), esc_html( $post->post_title ) )
			);
		}

		// Notify mentioned users
		foreach ( self::parse_user_mentions( $commentdata['comment_content'] ) as $uid ) {
			if ( $uid !== $commenter_id ) {
				NotificationManager::add(
					$uid,
					'mention',
					$post->ID,
					$commenter_id,
                                        sprintf( esc_html__( '%1$s mentioned you in a comment.', 'artpulse' ), esc_html( $commentdata['comment_author'] ) )
				);
			}
		}
	}

	/**
	 * Notify user on membership level change.
	 */
	public static function notify_on_membership_change( $user_id, $old_level, $new_level, $change_type ) {
		NotificationManager::add(
			$user_id,
			'membership_' . $change_type,
			null,
			null,
                        sprintf(
                                esc_html__( 'Your membership was %1$s: %2$s → %3$s.', 'artpulse' ),
                                esc_html( $change_type ),
                                esc_html( $old_level ),
                                esc_html( $new_level )
                        )
		);
	}

	/**
	 * Notify user of payment-related events.
	 */
	public static function notify_on_payment( $user_id, $amount, $currency, $event_type ) {
		$amount_display = number_format_i18n( $amount, 2 ) . ' ' . strtoupper( $currency );
		NotificationManager::add(
			$user_id,
			'payment_' . $event_type,
			null,
			null,
                        sprintf( esc_html__( 'Payment %1$s: %2$s.', 'artpulse' ), esc_html( $event_type ), esc_html( $amount_display ) )
		);
	}

	/**
	 * Notify event organizer when an RSVP is added.
	 */
	public static function notify_on_rsvp( $event_id, $rsvping_user_id ) {
		$event = get_post( $event_id );
		if ( ! $event || $event->post_type !== 'artpulse_event' ) {
			return;
		}

		$organizer_id = intval( $event->post_author );
		if ( ! $organizer_id || $organizer_id === intval( $rsvping_user_id ) ) {
			return;
		}

		NotificationManager::add(
			$organizer_id,
			'rsvp_received',
			$event_id,
			$rsvping_user_id
		);
	}

	/**
	 * Notify event organizer when an event status changes.
	 */
	public static function notify_on_event_status( $new_status, $old_status, $post ) {
		if ( $post->post_type !== 'artpulse_event' ) {
			return;
		}

		if ( $old_status === 'pending' && $new_status === 'publish' ) {
			NotificationManager::add(
				$post->post_author,
				'event_approved',
				$post->ID
			);
		} elseif ( $old_status === 'pending' && in_array( $new_status, array( 'trash', 'rejected' ), true ) ) {
			NotificationManager::add(
				$post->post_author,
				'event_rejected',
				$post->ID
			);
		}
	}

	private static function parse_user_mentions( string $content ): array {
		preg_match_all( '/@([A-Za-z0-9_]+)/', $content, $m );
		$ids = array();
		foreach ( array_unique( $m[1] ?? array() ) as $name ) {
			$user = get_user_by( 'slug', $name ) ?: get_user_by( 'login', $name );
			if ( $user ) {
				$ids[] = (int) $user->ID;
			}
		}
		return $ids;
	}
}
