<?php
namespace ArtPulse\Engagement\Tests;

use ArtPulse\Engagement\DigestMailer;

/**
 * @group ENGAGEMENT
 */
class DigestMailerTest extends \WP_UnitTestCase {

	/**
	 * Test the is_digest_day calculation.
	 */
	public function test_is_digest_day_weekly_and_monthly(): void {
		$user_id = self::factory()->user->create();

		update_user_meta( $user_id, 'ap_digest_frequency', 'weekly' );
		$monday = strtotime( 'monday this week' );
		// Pretend it's Monday.
		add_filter(
			'current_time',
			function ( $timestamp, $type ) use ( $monday ) {
				return $type === 'timestamp' ? $monday : $timestamp;
			},
			10,
			2
		);
		$this->assertTrue( DigestMailer::is_digest_day( $user_id ) );
		remove_all_filters( 'current_time' );

		update_user_meta( $user_id, 'ap_digest_frequency', 'monthly' );
		$first = strtotime( 'first day of this month' );
		add_filter(
			'current_time',
			function ( $timestamp, $type ) use ( $first ) {
				return $type === 'timestamp' ? $first : $timestamp;
			},
			10,
			2
		);
		$this->assertTrue( DigestMailer::is_digest_day( $user_id ) );
		remove_all_filters( 'current_time' );
	}
}
