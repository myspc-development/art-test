<?php
namespace ArtPulse\Core;

class ArtworkEventLinkManager
{
    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_artwork_event_links';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            artwork_id BIGINT NOT NULL,
            event_id BIGINT NOT NULL,
            exhibited_at DATETIME NULL,
            UNIQUE KEY artwork_event (artwork_id, event_id),
            KEY artwork_id (artwork_id),
            KEY event_id (event_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        error_log($sql);
        dbDelta($sql);
    }

    /**
     * Ensure the artwork event link table exists.
     */
    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_artwork_event_links';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function link(int $artwork_id, int $event_id, ?string $exhibited_at = null): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_artwork_event_links';
        $wpdb->replace(
            $table,
            [
                'artwork_id'   => $artwork_id,
                'event_id'     => $event_id,
                'exhibited_at' => $exhibited_at,
            ],
            [
                '%d', '%d', $exhibited_at ? '%s' : '%s'
            ]
        );
    }

    public static function unlink(int $artwork_id, int $event_id): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_artwork_event_links';
        $wpdb->delete($table, ['artwork_id' => $artwork_id, 'event_id' => $event_id], ['%d', '%d']);
    }

    public static function get_events_for_artwork(int $artwork_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_artwork_event_links';
        return $wpdb->get_results(
            $wpdb->prepare("SELECT event_id, exhibited_at FROM $table WHERE artwork_id = %d", $artwork_id)
        );
    }

    public static function get_artworks_for_event(int $event_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_artwork_event_links';
        return $wpdb->get_results(
            $wpdb->prepare("SELECT artwork_id, exhibited_at FROM $table WHERE event_id = %d", $event_id)
        );
    }
}
