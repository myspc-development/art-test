<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration to unify ap_webhook_logs schema.
 */
function ap_unify_webhook_logs_migration( ?string $old_version = null, ?string $new_version = null ): void {
	global $wpdb;
	$table  = $wpdb->prefix . 'ap_webhook_logs';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return; // table not present
	}

	$columns = $wpdb->get_results( "SHOW COLUMNS FROM $table", ARRAY_A );
	if ( ! $columns ) {
		return;
	}
	$names = wp_list_pluck( $columns, 'Field' );

	if ( in_array( 'status', $names, true ) && ! in_array( 'status_code', $names, true ) ) {
		$wpdb->query( "ALTER TABLE $table ADD status_code VARCHAR(20) NULL" );
		$wpdb->query( "UPDATE $table SET status_code = status" );
		$wpdb->query( "ALTER TABLE $table DROP COLUMN status" );
	}
	if ( in_array( 'response', $names, true ) && ! in_array( 'response_body', $names, true ) ) {
		$wpdb->query( "ALTER TABLE $table ADD response_body TEXT NULL" );
		$wpdb->query( "UPDATE $table SET response_body = response" );
		$wpdb->query( "ALTER TABLE $table DROP COLUMN response" );
	}
	if ( in_array( 'created_at', $names, true ) && ! in_array( 'timestamp', $names, true ) ) {
		$wpdb->query( "ALTER TABLE $table ADD timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP" );
		$wpdb->query( "UPDATE $table SET timestamp = created_at" );
		$wpdb->query( "ALTER TABLE $table DROP COLUMN created_at" );
	}
	if ( ! in_array( 'subscription_id', $names, true ) ) {
		$wpdb->query( "ALTER TABLE $table ADD subscription_id BIGINT NOT NULL DEFAULT 0" );
	}
	$wpdb->query( "ALTER TABLE $table MODIFY subscription_id BIGINT NOT NULL" );
	if ( in_array( 'event', $names, true ) ) {
		$wpdb->query( "ALTER TABLE $table DROP COLUMN event" );
	}
	if ( in_array( 'payload', $names, true ) ) {
		$wpdb->query( "ALTER TABLE $table DROP COLUMN payload" );
	}

	$index = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'sub_id'" );
	if ( ! $index ) {
		$wpdb->query( "ALTER TABLE $table ADD KEY sub_id (subscription_id)" );
	}
}
