<?php
namespace ArtPulse\Core;

class FeedbackManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('wp_ajax_ap_submit_feedback', [self::class, 'handle_submission']);
        add_action('wp_ajax_nopriv_ap_submit_feedback', [self::class, 'handle_submission']);
    }

    /**
     * Ensure the feedback table exists.
     */
    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_feedback';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY (id),
            user_id BIGINT NULL,
            type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            email VARCHAR(100) DEFAULT NULL,
            tags VARCHAR(255) DEFAULT NULL,
            context TEXT DEFAULT NULL,
            votes INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'planned',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            KEY user_id (user_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $comments = $wpdb->prefix . 'ap_feedback_comments';
        $sql2 = "CREATE TABLE $comments (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY(id),
            feedback_id BIGINT NOT NULL,
            user_id BIGINT NULL,
            comment TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY feedback_id (feedback_id),
            KEY user_id (user_id)
        ) $charset;";
        dbDelta($sql2);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_feedback';
        $comments = $wpdb->prefix . 'ap_feedback_comments';
        $exists  = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        $exists2 = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $comments));
        if ($exists !== $table || $exists2 !== $comments) {
            self::install_table();
        }
    }

    public static function handle_submission(): void
    {
        check_ajax_referer('ap_feedback_nonce', 'nonce');

        $type = sanitize_text_field($_POST['type'] ?? 'general');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $tags = sanitize_text_field($_POST['tags'] ?? '');
        $context = sanitize_text_field($_POST['context'] ?? '');
        if (empty($description)) {
            wp_send_json_error(['message' => __('Description required.', 'artpulse')]);
        }
        $user_id = get_current_user_id() ?: null;
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feedback';
        $wpdb->insert($table, [
            'user_id'     => $user_id,
            'type'        => $type,
            'description' => $description,
            'email'       => $email,
            'tags'        => $tags,
            'context'     => $context,
            'created_at'  => current_time('mysql'),
        ]);
        wp_send_json_success();
    }

    public static function upvote(int $id, int $user_id): int
    {
        $voted = get_user_meta($user_id, 'ap_feedback_votes', true);
        if (!is_array($voted)) {
            $voted = [];
        }
        if (in_array($id, $voted, true)) {
            return self::get_votes($id);
        }
        $voted[] = $id;
        update_user_meta($user_id, 'ap_feedback_votes', $voted);

        global $wpdb;
        $table = $wpdb->prefix . 'ap_feedback';
        $wpdb->query($wpdb->prepare("UPDATE $table SET votes = votes + 1 WHERE id = %d", $id));

        return self::get_votes($id);
    }

    public static function get_votes(int $id): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feedback';
        return (int) $wpdb->get_var($wpdb->prepare("SELECT votes FROM $table WHERE id = %d", $id));
    }

    public static function add_comment(int $feedback_id, int $user_id, string $comment): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feedback_comments';
        $wpdb->insert($table, [
            'feedback_id' => $feedback_id,
            'user_id'     => $user_id ?: null,
            'comment'     => $comment,
            'created_at'  => current_time('mysql'),
        ]);
    }

    public static function get_comments(int $feedback_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_feedback_comments';
        $rows = $wpdb->get_results($wpdb->prepare("SELECT user_id, comment, created_at FROM $table WHERE feedback_id = %d ORDER BY created_at ASC", $feedback_id), ARRAY_A);

        return array_map(static function($row) {
            $user = $row['user_id'] ? get_userdata((int) $row['user_id']) : null;
            return [
                'user_id'    => (int) $row['user_id'],
                'author'     => $user ? $user->display_name : '',
                'comment'    => $row['comment'],
                'created_at' => $row['created_at'],
            ];
        }, $rows);
    }
}
