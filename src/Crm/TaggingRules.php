<?php
namespace ArtPulse\Crm;

class TaggingRules {

	public static function register(): void {
		add_action( 'ap_event_rsvp_added', array( self::class, 'handle_rsvp' ), 10, 2 );
		add_action( 'ap_follow_added', array( self::class, 'handle_follow' ), 10, 3 );
		add_action( 'ap_donation_recorded', array( self::class, 'handle_donation' ), 10, 3 );
	}

	public static function handle_rsvp( int $event_id, int $user_id ): void {
		$org_id = intval( get_post_meta( $event_id, '_ap_event_organization', true ) );
		if ( ! $org_id ) {
			return;
		}
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		ContactModel::add_or_update( $org_id, $user->user_email, $user->display_name, array( 'rsvp' ) );
	}

	public static function handle_follow( int $user_id, int $object_id, string $object_type ): void {
		if ( $object_type !== 'user' ) {
			return;
		}
		$org_id = intval( get_user_meta( $object_id, 'ap_organization_id', true ) );
		if ( ! $org_id ) {
			return;
		}
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		ContactModel::add_or_update( $org_id, $user->user_email, $user->display_name, array( 'follower' ) );
	}

	public static function handle_donation( int $org_id, int $user_id, float $amount ): void {
		$user = get_user_by( 'id', $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		ContactModel::add_or_update( $org_id, $user->user_email, $user->display_name, array( 'donor' ) );
	}
}
