<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-roles.php';
require_once __DIR__ . '/helpers-page.php';

/**
 * Handles plugin activation tasks such as installing roles and pages.
 */
class ArtPulse_Activator
{
    /**
     * Run on plugin activation.
     */
    public static function activate(): void
    {
        self::create_tables();
        ArtPulse_Roles::install();
        artpulse_create_required_pages();
        flush_rewrite_rules();
    }

    /**
     * Create or upgrade custom database tables.
     *
     * Schemas will be fleshed out in future milestones; this
     * stub simply ensures dbDelta() runs without errors.
     */
    private static function create_tables(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_placeholder';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
