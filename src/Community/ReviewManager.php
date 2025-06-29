<?php
namespace ArtPulse\Community;

class ReviewManager {
    public static function install_reviews_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_reviews';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT NOT NULL,
            target_id BIGINT NOT NULL,
            target_type VARCHAR(32) NOT NULL,
            rating TINYINT NOT NULL,
            review_text TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_target (user_id, target_id, target_type),
            KEY target (target_id, target_type)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function add_review(int $user_id, int $target_id, string $target_type, int $rating, string $text = ''): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_reviews';
        $rating = max(1, min(5, $rating));
        $wpdb->replace($table, [
            'user_id'     => $user_id,
            'target_id'   => $target_id,
            'target_type' => $target_type,
            'rating'      => $rating,
            'review_text' => $text,
            'created_at'  => current_time('mysql'),
        ]);
    }

    public static function get_reviews(int $target_id, string $target_type, int $limit = 20): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_reviews';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE target_id = %d AND target_type = %s ORDER BY created_at DESC LIMIT %d",
            $target_id,
            $target_type,
            $limit
        ), ARRAY_A);
    }

    public static function get_average_rating(int $target_id, string $target_type): float
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_reviews';
        $avg = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(rating) FROM $table WHERE target_id = %d AND target_type = %s",
            $target_id,
            $target_type
        ));
        return $avg ? (float) $avg : 0.0;
    }
}
