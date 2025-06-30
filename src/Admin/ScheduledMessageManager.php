<?php
namespace ArtPulse\Admin;

use ArtPulse\Admin\OrgCommunicationsCenter;

/**
 * Manage scheduled messages for organizations.
 */
class ScheduledMessageManager
{
    /**
     * Register cron event and table check.
     */
    public static function register(): void
    {
        add_action('admin_init', [self::class, 'maybe_install_table']);
        add_action('ap_process_scheduled_messages', [self::class, 'process_due_messages']);
        self::schedule_cron();
    }

    /**
     * Create scheduled messages table.
     */
    public static function install_scheduled_table(): void
    {
        global $wpdb;
        $table          = $wpdb->prefix . 'ap_scheduled_messages';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY (id),
            org_id BIGINT NOT NULL,
            sender_id BIGINT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            send_at DATETIME NOT NULL,
            channels TEXT NOT NULL,
            segments TEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            KEY org_id (org_id),
            KEY status (status),
            KEY send_at (send_at)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Ensure the table exists.
     */
    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_scheduled_messages';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_scheduled_table();
        }
    }

    /**
     * Schedule the cron event for processing messages.
     */
    public static function schedule_cron(): void
    {
        if (!wp_next_scheduled('ap_process_scheduled_messages')) {
            wp_schedule_event(time(), 'hourly', 'ap_process_scheduled_messages');
        }
    }

    /**
     * Clear the cron event.
     */
    public static function clear_cron(): void
    {
        wp_clear_scheduled_hook('ap_process_scheduled_messages');
    }

    /**
     * Schedule a message for later delivery.
     *
     * @param int   $org_id
     * @param int   $sender_id
     * @param string $subject
     * @param string $body
     * @param int   $timestamp Unix timestamp for sending
     * @param array  $channels
     * @param array  $segments
     * @return int Insert ID
     */
    public static function schedule_message(
        int $org_id,
        int $sender_id,
        string $subject,
        string $body,
        int $timestamp,
        array $channels = ['in-app'],
        array $segments = []
    ): int {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_scheduled_messages';
        $wpdb->insert($table, [
            'org_id'    => $org_id,
            'sender_id' => $sender_id,
            'subject'   => $subject,
            'body'      => $body,
            'send_at'   => gmdate('Y-m-d H:i:s', $timestamp),
            'channels'  => maybe_serialize($channels),
            'segments'  => maybe_serialize($segments),
            'status'    => 'pending',
        ]);
        return (int) $wpdb->insert_id;
    }

    /**
     * Process due scheduled messages.
     */
    public static function process_due_messages(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_scheduled_messages';
        $now   = current_time('mysql');
        $rows  = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE status = %s AND send_at <= %s",
            'pending',
            $now
        ), ARRAY_A);

        foreach ($rows as $row) {
            $recipients = self::get_recipients((int) $row['org_id'], maybe_unserialize($row['segments']));
            foreach ($recipients as $user_id) {
                OrgCommunicationsCenter::insert_message([
                    'org_id'    => (int) $row['org_id'],
                    'user_from' => (int) $row['sender_id'],
                    'user_to'   => (int) $user_id,
                    'subject'   => $row['subject'],
                    'body'      => $row['body'],
                    'thread_id' => null,
                ]);
            }
            $wpdb->update($table, ['status' => 'sent'], ['id' => $row['id']]);
        }
    }

    /**
     * Simple recipient lookup. Returns follower IDs of the org.
     *
     * @param int   $org_id
     * @param array $segments
     * @return array<int>
     */
    private static function get_recipients(int $org_id, array $segments): array
    {
        // Basic example: return all follower user IDs stored in user meta
        $followers = get_post_meta($org_id, 'ap_follower_ids', true);
        if (!is_array($followers)) {
            $followers = [];
        }
        return array_map('intval', $followers);
    }
}
