<?php

/**

 * @group INTEGRATION
 */

class DbTablesExistTest extends WP_UnitTestCase {
	public function test_all_custom_tables_exist() {
		global $wpdb;
		$tables = array(
			'ap_auctions',
			'ap_bids',
			'ap_donations',
			'ap_event_tickets',
			'ap_feedback',
			'ap_feedback_comments',
			'ap_messages',
			'ap_org_messages',
			'ap_org_user_roles',
			'ap_payouts',
			'ap_promotions',
			'ap_roles',
			'ap_rsvps',
			'ap_scheduled_messages',
			'ap_tickets',
		);
		foreach ( $tables as $t ) {
			$full   = $wpdb->prefix . $t;
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full ) );
			$this->assertSame( $full, $exists, "Missing table: {$full}" );
		}
	}
}
