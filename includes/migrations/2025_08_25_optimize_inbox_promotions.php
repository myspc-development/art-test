<?php
if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Add indexes to messaging and promotions tables for performance.
 */
function ap_optimize_inbox_promotions_migration( ?string $old_version = null, ?string $new_version = null ): void {
        global $wpdb;

        // Messages table: ensure index on created_at for ordering inbox queries.
        $table  = $wpdb->prefix . 'ap_messages';
        $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
        if ( $exists === $table ) {
                $idx = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'created_at'" );
                if ( ! $idx ) {
                        $wpdb->query( "ALTER TABLE $table ADD KEY created_at (created_at)" );
                }
        }

        // Promotions table: ensure index on priority_level and start_date.
        $table  = $wpdb->prefix . 'ap_promotions';
        $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
        if ( $exists === $table ) {
                $idx = $wpdb->get_var( "SHOW INDEX FROM $table WHERE Key_name = 'priority_start'" );
                if ( ! $idx ) {
                        $wpdb->query( "ALTER TABLE $table ADD KEY priority_start (priority_level, start_date)" );
                }
        }
}
