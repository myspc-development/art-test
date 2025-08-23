<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create or upgrade the webhook logs table to the unified schema.
 */
function artpulse_create_webhook_logs_table(): void {
    global $wpdb;
    $table = $wpdb->prefix . 'ap_webhook_logs';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        subscription_id BIGINT NOT NULL,
        status_code VARCHAR(20) NULL,
        response_body TEXT NULL,
        timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY sub_id (subscription_id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    if (defined('WP_DEBUG') && WP_DEBUG) {
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        error_log(($exists === $table ? '✅' : '❌') . " Table $table " . ($exists === $table ? 'created' : 'not created'));
    }
}
