<?php
namespace ArtPulse\DB;

use WPDB;

/**
 * Create a table only if it does not already exist.
 *
 * @param string $table_name Full table name with $wpdb prefix.
 * @param string $schema     CREATE TABLE statement without "CREATE TABLE".
 */
function ap_maybe_create_table(string $table_name, string $schema): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Bail if the table already exists.
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name))) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE $table_name ( $schema ) $charset_collate;");
}

function create_monetization_tables() {
    $installed = get_option('ap_db_version', '0.0.0');
    if (version_compare($installed, '1.4.0', '>=')) {
        return;
    }

    global $wpdb;
    $payouts       = "{$wpdb->prefix}ap_payouts";
    $tickets       = "{$wpdb->prefix}ap_tickets";
    $event_tickets = "{$wpdb->prefix}ap_event_tickets";
    $auctions      = "{$wpdb->prefix}ap_auctions";
    $bids          = "{$wpdb->prefix}ap_bids";
    $promotions    = "{$wpdb->prefix}ap_promotions";
    $messages      = "{$wpdb->prefix}ap_messages";

    ap_maybe_create_table($payouts, "
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        artist_id BIGINT NOT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        method VARCHAR(50) NOT NULL DEFAULT '',
        payout_date DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY artist_id (artist_id)
    ");

    ap_maybe_create_table($tickets, "
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
    ");

    ap_maybe_create_table($event_tickets, "
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
    ");

    ap_maybe_create_table($auctions, "
        artwork_id BIGINT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        reserve_price DECIMAL(10,2) NULL,
        buy_now_price DECIMAL(10,2) NULL,
        min_increment DECIMAL(10,2) NOT NULL DEFAULT 1,
        starting_bid DECIMAL(10,2) NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (artwork_id)
    ");

    ap_maybe_create_table($bids, "
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT NOT NULL,
        artwork_id BIGINT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY artwork_id (artwork_id),
        KEY user_id (user_id)
    ");

    ap_maybe_create_table($promotions, "
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        artwork_id BIGINT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'featured',
        priority_level TINYINT NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY artwork_id (artwork_id),
        KEY start_end (start_date, end_date)
    ");

    ap_maybe_create_table($messages, "
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        sender_id BIGINT UNSIGNED NOT NULL,
        receiver_id BIGINT UNSIGNED NOT NULL,
        content TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ");

    // Ensure AUTO_INCREMENT is properly set for existing installs without
    // attempting to redefine the primary key.
    $has_pk = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(1) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND CONSTRAINT_TYPE = 'PRIMARY KEY'",
            $wpdb->prefix . 'ap_payouts'
        )
    );
    if (0 === intval($has_pk)) {
        $wpdb->query("ALTER TABLE {$wpdb->prefix}ap_payouts CHANGE id id BIGINT AUTO_INCREMENT PRIMARY KEY");
    }

    validate_monetization_tables();

    update_option('artpulse_installed', true);
    update_option('artpulse_version', defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : null);
    update_option('artpulse_db_version', '1.4.0');
    update_option('ap_db_version', '1.4.0');
    if (!get_option('artpulse_install_time')) {
        update_option('artpulse_install_time', current_time('mysql'));
    }
}

function validate_monetization_tables(): void {
    global $wpdb;
    $required_tables = [
        $wpdb->prefix . 'ap_roles',
        $wpdb->prefix . 'ap_feedback',
        $wpdb->prefix . 'ap_feedback_comments',
        $wpdb->prefix . 'ap_org_messages',
        $wpdb->prefix . 'ap_scheduled_messages',
        $wpdb->prefix . 'ap_payouts',
        $wpdb->prefix . 'ap_auctions',
        $wpdb->prefix . 'ap_bids',
        $wpdb->prefix . 'ap_promotions',
        $wpdb->prefix . 'ap_messages',
    ];

    foreach ($required_tables as $tbl) {
        if ($wpdb->get_var("SHOW TABLES LIKE '{$tbl}'") !== $tbl) {
            error_log("❌ Missing: {$tbl}");
        } else {
            error_log("✅ Present: {$tbl}");
        }
    }
}
