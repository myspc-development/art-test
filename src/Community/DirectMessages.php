<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\EmailService;

class DirectMessages
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_messages';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_messages';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT AUTO_INCREMENT,
            PRIMARY KEY (id),
            sender_id BIGINT NOT NULL,
            recipient_id BIGINT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            KEY sender_id (sender_id),
            KEY recipient_id (recipient_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/messages', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'send'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'recipient_id' => [ 'type' => 'integer', 'required' => true ],
                'content'      => [ 'type' => 'string',  'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'fetch'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'with' => [ 'type' => 'integer', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/conversations', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'rest_list_conversations'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route('artpulse/v1', '/message/read', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'mark_read'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function send(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $sender_id    = get_current_user_id();
        $recipient_id = absint($req['recipient_id']);
        $content      = wp_kses_post($req['content']);

        if (!$recipient_id || $content === '' || !get_user_by('id', $recipient_id)) {
            return new WP_Error('invalid_params', 'Invalid recipient or content.', ['status' => 400]);
        }

        self::add_message($sender_id, $recipient_id, $content);

        return rest_ensure_response(['status' => 'sent']);
    }

    public static function fetch(WP_REST_Request $req): WP_REST_Response
    {
        $user_id  = get_current_user_id();
        $other_id = absint($req['with']);

        $messages = self::get_conversation($user_id, $other_id);

        return rest_ensure_response($messages);
    }

    public static function add_message(int $sender_id, int $recipient_id, string $content): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $wpdb->insert($table, [
            'sender_id'    => $sender_id,
            'recipient_id' => $recipient_id,
            'content'      => $content,
            'created_at'   => current_time('mysql'),
            'is_read'      => 0,
        ]);
        $id = (int) $wpdb->insert_id;

        $recipient = get_user_by('id', $recipient_id);
        $sender    = get_user_by('id', $sender_id);
        if ($recipient && $sender && is_email($recipient->user_email)) {
            $subject = sprintf('New message from %s', $sender->display_name);
            EmailService::send($recipient->user_email, $subject, $content);
        }

        return $id;
    }

    public static function get_conversation(int $user_id, int $other_user): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $sql = "SELECT * FROM $table WHERE (sender_id = %d AND recipient_id = %d) OR (sender_id = %d AND recipient_id = %d) ORDER BY created_at ASC";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $user_id, $other_user, $other_user, $user_id), ARRAY_A);
        return array_map(static function($row){
            $row['id'] = (int) $row['id'];
            $row['sender_id'] = (int) $row['sender_id'];
            $row['recipient_id'] = (int) $row['recipient_id'];
            $row['is_read'] = (int) $row['is_read'];
            return $row;
        }, $rows);
    }

    public static function list_conversations(int $user_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $sql   = "SELECT DISTINCT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS other_id FROM $table WHERE sender_id = %d OR recipient_id = %d";
        $rows  = $wpdb->get_col($wpdb->prepare($sql, $user_id, $user_id, $user_id));
        return array_map('intval', $rows);
    }

    public static function rest_list_conversations(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $list    = self::list_conversations($user_id);
        return rest_ensure_response($list);
    }

    public static function mark_read(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $ids = $req->get_param('ids');
        if (!is_array($ids)) {
            $id  = $req->get_param('id');
            $ids = $id ? [$id] : [];
        }
        $ids = array_map('intval', $ids);
        if (!$ids) {
            return new WP_Error('invalid_params', 'No message IDs', ['status' => 400]);
        }

        global $wpdb;
        $table   = $wpdb->prefix . 'ap_messages';
        $user_id = get_current_user_id();
        $place   = implode(',', array_fill(0, count($ids), '%d'));
        $args    = array_merge($ids, [$user_id]);
        $sql     = "UPDATE $table SET is_read = 1 WHERE id IN ($place) AND recipient_id = %d";
        $wpdb->query($wpdb->prepare($sql, ...$args));

        return rest_ensure_response(['updated' => count($ids)]);
    }
}
