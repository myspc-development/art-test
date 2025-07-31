<?php
if (!defined('ABSPATH')) {
    exit;
}

function artpulse_create_webhook_logs_table(): void {
    global $wpdb;
    $table = $wpdb->prefix . 'ap_webhook_logs';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        event VARCHAR(255) NOT NULL,
        payload LONGTEXT,
        status VARCHAR(50) DEFAULT NULL,
        response TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    if (defined('WP_DEBUG') && WP_DEBUG) {
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        error_log(($exists === $table ? '✅' : '❌') . " Table $table " . ($exists === $table ? 'created' : 'not created'));
    }
}
