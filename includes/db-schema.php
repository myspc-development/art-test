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
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        artist_id BIGINT NOT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        method VARCHAR(50) NOT NULL DEFAULT '',
        payout_date DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY artist_id (artist_id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $tickets (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT NOT NULL,
        event_id BIGINT NOT NULL,
        ticket_tier_id BIGINT NOT NULL,
        code VARCHAR(64) NOT NULL,
        purchase_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        UNIQUE KEY code (code),
        KEY user_id (user_id),
        KEY event_id (event_id),
        PRIMARY KEY (id)
    ) $charset_collate;");

    dbDelta("CREATE TABLE $event_tickets (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id BIGINT NOT NULL,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        inventory INT NOT NULL DEFAULT 0,
        sold INT NOT NULL DEFAULT 0,
        start_date DATETIME NULL,
        end_date DATETIME NULL,
        product_id BIGINT NULL,
        stripe_price_id VARCHAR(255) NULL,
        tier_order INT NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY event_id (event_id)
    ) $charset_collate;");
}
