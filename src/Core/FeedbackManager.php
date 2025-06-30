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
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            KEY user_id (user_id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_feedback';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
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
}
