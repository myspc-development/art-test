<?php
namespace ArtPulse\DB;

use WPDB;

function create_monetization_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $payouts       = "{$wpdb->prefix}ap_payouts";
    $tickets       = "{$wpdb->prefix}ap_tickets";
    $event_tickets = "{$wpdb->prefix}ap_event_tickets";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta("CREATE TABLE $payouts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        artist_id BIGINT UNSIGNED NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        payout_date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;");

    dbDelta("CREATE TABLE $tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id BIGINT UNSIGNED NOT NULL,
        ticket_tier_id INT,
        status VARCHAR(20) DEFAULT 'active',
        purchaser_id BIGINT,
        purchased_at DATETIME
    ) $charset_collate;");

    dbDelta("CREATE TABLE $event_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id BIGINT UNSIGNED NOT NULL,
        label VARCHAR(255),
        price DECIMAL(10,2),
        quantity INT
    ) $charset_collate;");
}
