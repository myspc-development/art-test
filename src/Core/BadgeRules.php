<?php
namespace ArtPulse\Core;

use ArtPulse\Community\ReferralManager;

class BadgeRules {

	public static function register(): void {
		add_action( 'ap_event_rsvp_added', array( self::class, 'check_rsvp_badges' ), 10, 2 );
		add_action( 'ap_referral_redeemed', array( self::class, 'check_referrals_badge' ) );
	}

	public static function check_rsvp_badges( int $event_id, int $user_id ): void {
		// Award host when event reaches 100 RSVPs
		$host  = (int) get_post_field( 'post_author', $event_id );
		$count = (int) get_post_meta( $event_id, 'ap_rsvp_count', true );
		++$count;
		update_post_meta( $event_id, 'ap_rsvp_count', $count );
		if ( $count >= 100 ) {
			UserDashboardManager::addBadge( $host, '100-rsvps' );
		}

		// Streak badge for users RSVPing to 3 events in last 30 days
		$ids = get_user_meta( $user_id, 'ap_rsvp_events', true );
		if ( ! is_array( $ids ) ) {
			return;
		}
		$recent = 0;
		foreach ( $ids as $eid ) {
			$date = get_post_meta( $eid, '_ap_event_date', true );
			if ( $date && strtotime( $date ) >= strtotime( '-30 days' ) ) {
				++$recent;
			}
		}
		if ( $recent >= 3 ) {
			UserDashboardManager::addBadge( $user_id, 'streak-3-in-30' );
		}
	}

	public static function check_referrals_badge( int $referrer_id ): void {
		$count = ReferralManager::get_referral_count( $referrer_id );
		if ( $count >= 3 ) {
			UserDashboardManager::addBadge( $referrer_id, 'connector' );
		}
	}
}
