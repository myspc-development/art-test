<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add missing indexes for performance.
 */
function ap_add_missing_indexes_migration( ?string $old_version = null, ?string $new_version = null ): void {
	global $wpdb;

	// Ensure created_at index on event chat
	$table  = $wpdb->prefix . 'ap_event_chat';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists === $table ) {
		$idx = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'created_at'" );
		if ( ! $idx ) {
			$wpdb->query( "ALTER TABLE $table ADD KEY created_at (created_at)" );
		}
	}

	// Ensure timestamp and subscription_id indexes on webhook logs
	$table  = $wpdb->prefix . 'ap_webhook_logs';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists === $table ) {
		$idx = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'ts'" );
		if ( ! $idx ) {
			$wpdb->query( "ALTER TABLE $table ADD KEY ts (timestamp)" );
		}
		$idx = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'sub_id'" );
		if ( ! $idx ) {
			$wpdb->query( "ALTER TABLE $table ADD KEY sub_id (subscription_id)" );
		}
	}

	// Ensure user_id index on org user roles
	$table  = $wpdb->prefix . 'ap_org_user_roles';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists === $table ) {
		$idx = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'user_id'" );
		if ( ! $idx ) {
			$wpdb->query( "ALTER TABLE $table ADD KEY user_id (user_id)" );
		}
	}
}
