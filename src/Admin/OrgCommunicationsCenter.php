<?php
namespace ArtPulse\Admin;

/**
 * Unified Communications Center for organization messages.
 */
class OrgCommunicationsCenter
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'maybe_install_table']);
    }

    /**
     * Create the messages table if it does not exist.
     */
    public static function install_messages_table(): void
    {
        global $wpdb;
        $table          = $wpdb->prefix . 'ap_org_messages';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            org_id BIGINT NOT NULL,
            event_id BIGINT NULL,
            user_from BIGINT NULL,
            user_to BIGINT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(16) NOT NULL DEFAULT 'unread',
            thread_id BIGINT NULL,
            KEY org_id (org_id),
            KEY event_id (event_id),
            KEY user_to (user_to),
            KEY status (status),
            KEY thread_id (thread_id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Ensure the messages table exists.
     */
    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_org_messages';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_messages_table();
        }
    }

    /**
     * Fetch recent messages for an organization.
     *
     * @param int $org_id Organization post ID.
     * @param int $limit  Number of messages to retrieve.
     * @return array<int, array<string, mixed>>
     */
    public static function get_messages_for_org(int $org_id, int $limit = 50): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_org_messages';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE org_id = %d ORDER BY created_at DESC LIMIT %d",
                $org_id,
                $limit
            ),
            ARRAY_A
        );
        return $results ?: [];
    }
}
