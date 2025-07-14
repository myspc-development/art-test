<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\EmailService;
use ArtPulse\Community\CommunityRoles;

class DirectMessages
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('init', [self::class, 'maybe_install_flags_table']);
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
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            sender_id BIGINT NOT NULL,
            recipient_id BIGINT NOT NULL,
            content TEXT NOT NULL,
            context_type VARCHAR(32) NULL,
            context_id BIGINT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            is_delivered TINYINT(1) NOT NULL DEFAULT 1,
            KEY sender_id (sender_id),
            KEY recipient_id (recipient_id),
            KEY context (context_type, context_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function maybe_install_flags_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_message_flags';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_flags_table();
        }
    }

    public static function install_flags_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_message_flags';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            message_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            action VARCHAR(20) NOT NULL,
            note TEXT NULL,
            logged_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY message_id (message_id),
            KEY user_id (user_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function log_flag(int $message_id, int $user_id, string $action, string $note = ''): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_message_flags';
        $wpdb->insert($table, [
            'message_id' => $message_id,
            'user_id'    => $user_id,
            'action'     => $action,
            'note'       => $note,
            'logged_at'  => current_time('mysql'),
        ]);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/messages/send', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'send_v2'],
            'permission_callback' => [self::class, 'permission_send'],
            'args'                => [
                'recipient_id' => [ 'type' => 'integer', 'required' => true ],
                'content'      => [ 'type' => 'string',  'required' => true ],
                'context_type' => [ 'type' => 'string',  'required' => false ],
                'context_id'   => [ 'type' => 'integer', 'required' => false ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'send'],
            'permission_callback' => [self::class, 'permission_send'],
            'args'                => [
                'recipient_id' => [ 'type' => 'integer', 'required' => true ],
                'content'      => [ 'type' => 'string',  'required' => true ],
                'nonce'        => [ 'type' => 'string',  'required' => true ],
                'context_type' => [ 'type' => 'string',  'required' => false ],
                'context_id'   => [ 'type' => 'integer', 'required' => false ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'fetch'],
            'permission_callback' => static function () {
                return current_user_can('read');
            },
            'args'                => [
                'with' => [ 'type' => 'integer', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages/updates', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'updates'],
            'permission_callback' => [self::class, 'permission_view'],
            'args'                => [
                'since'       => [ 'type' => 'string', 'required' => true ],
                'context_id'  => [ 'type' => 'integer', 'required' => false ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages/seen', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'seen'],
            'permission_callback' => [self::class, 'permission_view'],
            'args'                => [
                'message_ids' => [ 'type' => 'array', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages/search', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'search'],
            'permission_callback' => [self::class, 'permission_view'],
            'args'                => [
                'q' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/messages/context/(?P<type>[a-zA-Z0-9_-]+)/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'fetch_context'],
            'permission_callback' => [self::class, 'permission_view'],
        ]);

        register_rest_route('artpulse/v1', '/messages/block', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'block_user'],
            'permission_callback' => fn() => CommunityRoles::can_block(get_current_user_id()),
            'args'                => [
                'user_id' => [ 'type' => 'integer', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/conversations', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'rest_list_conversations'],
            'permission_callback' => static function () {
                return is_user_logged_in();
            },
        ]);

        register_rest_route('artpulse/v1', '/message/read', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'mark_read'],
            'permission_callback' => [self::class, 'permission_view'],
            'args'                => [
                'nonce' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);
    }

    public static function send(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $sender_id    = get_current_user_id();
        $recipient_id = absint($req['recipient_id']);
        $content      = wp_kses_post($req['content']);
        $context_type = $req->get_param('context_type') ? sanitize_key($req['context_type']) : null;
        $context_id   = $req->get_param('context_id') ? absint($req['context_id']) : null;

        if (!$recipient_id || $content === '' || !get_user_by('id', $recipient_id)) {
            return new WP_Error('invalid_params', 'Invalid recipient or content.', ['status' => 400]);
        }

        if (class_exists(__NAMESPACE__ . '\\BlockedUsers')) {
            if (BlockedUsers::is_blocked($recipient_id, $sender_id) || BlockedUsers::is_blocked($sender_id, $recipient_id)) {
                return new WP_Error('blocked', 'User has blocked messages.', ['status' => 403]);
            }
        }

        self::add_message($sender_id, $recipient_id, $content, $context_type, $context_id);

        return rest_ensure_response(['status' => 'sent']);
    }

    public static function send_v2(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $sender_id    = get_current_user_id();
        $recipient_id = absint($req['recipient_id']);
        $content      = wp_kses_post($req['content']);
        $context_type = $req->get_param('context_type') ? sanitize_key($req['context_type']) : null;
        $context_id   = $req->get_param('context_id') ? absint($req['context_id']) : null;

        if (!$recipient_id || $content === '' || !get_user_by('id', $recipient_id)) {
            return new WP_Error('invalid_params', 'Invalid recipient or content.', ['status' => 400]);
        }

        if (class_exists(__NAMESPACE__ . '\\BlockedUsers')) {
            if (BlockedUsers::is_blocked($recipient_id, $sender_id) || BlockedUsers::is_blocked($sender_id, $recipient_id)) {
                return new WP_Error('blocked', 'User has blocked messages.', ['status' => 403]);
            }
        }

        $id = self::add_message($sender_id, $recipient_id, $content, $context_type, $context_id);
        $row = self::get_message($id);

        return rest_ensure_response($row);
    }

    public static function fetch(WP_REST_Request $req): WP_REST_Response
    {
        $user_id  = get_current_user_id();
        $other_id = absint($req['with']);

        $messages = self::get_conversation($user_id, $other_id);

        return rest_ensure_response($messages);
    }

    public static function updates(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $since   = $req->get_param('since');
        $context_id = $req->get_param('context_id') ? absint($req['context_id']) : null;

        if (is_numeric($since)) {
            $since = gmdate('Y-m-d H:i:s', (int) $since);
        } else {
            $since = sanitize_text_field($since);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $sql = "SELECT * FROM $table WHERE created_at > %s AND (sender_id = %d OR recipient_id = %d)";
        $args = [$since, $user_id, $user_id];
        if ($context_id) {
            $sql .= " AND context_id = %d";
            $args[] = $context_id;
        }
        $sql .= " ORDER BY created_at ASC";
        $rows = $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A);
        $messages = array_map(static function($row){
            $row['id'] = (int) $row['id'];
            $row['sender_id'] = (int) $row['sender_id'];
            $row['recipient_id'] = (int) $row['recipient_id'];
            $row['is_read'] = (int) $row['is_read'];
            $row['is_delivered'] = isset($row['is_delivered']) ? (int) $row['is_delivered'] : 1;
            return $row;
        }, $rows);

        return rest_ensure_response($messages);
    }

    public static function add_message(int $sender_id, int $recipient_id, string $content, ?string $context_type = null, ?int $context_id = null): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $wpdb->insert($table, [
            'sender_id'    => $sender_id,
            'recipient_id' => $recipient_id,
            'content'      => $content,
            'context_type' => $context_type,
            'context_id'   => $context_id,
            'created_at'   => current_time('mysql'),
            'is_read'      => 0,
            'is_delivered' => 1,
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

    public static function get_message(int $id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
        if (!$row) {
            return [];
        }
        $row['id'] = (int) $row['id'];
        $row['sender_id'] = (int) $row['sender_id'];
        $row['recipient_id'] = (int) $row['recipient_id'];
        $row['is_read'] = (int) $row['is_read'];
        $row['is_delivered'] = isset($row['is_delivered']) ? (int) $row['is_delivered'] : 1;
        return $row;
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
            $row['is_delivered'] = isset($row['is_delivered']) ? (int) $row['is_delivered'] : 1;
            return $row;
        }, $rows);
    }

    public static function get_context_conversation(int $user_id, string $context_type, int $context_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $sql = "SELECT * FROM $table WHERE context_type = %s AND context_id = %d AND (sender_id = %d OR recipient_id = %d) ORDER BY created_at ASC";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $context_type, $context_id, $user_id, $user_id), ARRAY_A);
        return array_map(static function($row){
            $row['id'] = (int) $row['id'];
            $row['sender_id'] = (int) $row['sender_id'];
            $row['recipient_id'] = (int) $row['recipient_id'];
            $row['is_read'] = (int) $row['is_read'];
            $row['is_delivered'] = isset($row['is_delivered']) ? (int) $row['is_delivered'] : 1;
            return $row;
        }, $rows);
    }

    public static function list_conversations(int $user_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $sql = "SELECT CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS other_id,
                       SUM(CASE WHEN recipient_id = %d AND is_read = 0 THEN 1 ELSE 0 END) AS unread
                FROM $table
                WHERE sender_id = %d OR recipient_id = %d
                GROUP BY other_id";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $user_id, $user_id, $user_id, $user_id), ARRAY_A);
        return array_map(static function($row) {
            $other_id = (int) $row['other_id'];
            $user     = get_user_by('id', $other_id);
            return [
                'user_id'      => $other_id,
                'unread'       => (int) $row['unread'],
                'display_name' => $user ? $user->display_name : '',
                'avatar'       => $user ? get_avatar_url($other_id, ['size' => 48]) : '',
            ];
        }, $rows);
    }

    public static function rest_list_conversations(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $list = self::list_conversations($user_id);
        return rest_ensure_response($list);
    }

    public static function permission_view(WP_REST_Request $req): bool
    {
        return is_user_logged_in();
    }

    public static function permission_send(WP_REST_Request $req): bool
    {
        return current_user_can('ap_send_messages') && self::permission_view($req);
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

        self::mark_read_ids($ids, get_current_user_id());

        return rest_ensure_response(['updated' => count($ids)]);
    }

    public static function seen(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $ids = $req->get_param('message_ids');
        if (!is_array($ids)) {
            return new WP_Error('invalid_params', 'message_ids must be array', ['status' => 400]);
        }
        $ids = array_map('intval', $ids);
        self::mark_read_ids($ids, get_current_user_id());

        return rest_ensure_response(['updated' => count($ids)]);
    }

    public static function mark_read_ids(array $ids, int $user_id): void
    {
        if (!$ids) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $place = implode(',', array_fill(0, count($ids), '%d'));
        $args  = array_merge($ids, [$user_id]);
        $sql   = "UPDATE $table SET is_read = 1 WHERE id IN ($place) AND recipient_id = %d";
        $wpdb->query($wpdb->prepare($sql, ...$args));
    }

    public static function mark_unread_ids(array $ids, int $user_id): void
    {
        if (!$ids) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $place = implode(',', array_fill(0, count($ids), '%d'));
        $args  = array_merge($ids, [$user_id]);
        $sql   = "UPDATE $table SET is_read = 0 WHERE id IN ($place) AND recipient_id = %d";
        $wpdb->query($wpdb->prepare($sql, ...$args));
    }

    public static function search(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $term    = sanitize_text_field($req->get_param('q'));

        global $wpdb;
        $table = $wpdb->prefix . 'ap_messages';
        $like  = '%' . $wpdb->esc_like($term) . '%';
        $sql = "SELECT * FROM (SELECT *, CASE WHEN sender_id = %d THEN recipient_id ELSE sender_id END AS other_id FROM $table WHERE (sender_id = %d OR recipient_id = %d) AND content LIKE %s ORDER BY created_at DESC) t GROUP BY other_id";
        $rows = $wpdb->get_results($wpdb->prepare($sql, $user_id, $user_id, $user_id, $like), ARRAY_A);
        $messages = array_map(static function($row){
            $row['id'] = (int) $row['id'];
            $row['sender_id'] = (int) $row['sender_id'];
            $row['recipient_id'] = (int) $row['recipient_id'];
            $row['is_read'] = (int) $row['is_read'];
            $row['is_delivered'] = isset($row['is_delivered']) ? (int) $row['is_delivered'] : 1;
            return $row;
        }, $rows);

        return rest_ensure_response($messages);
    }

    public static function fetch_context(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $type    = sanitize_key($req['type']);
        $id      = absint($req['id']);
        $messages = self::get_context_conversation($user_id, $type, $id);
        return rest_ensure_response($messages);
    }

    public static function block_user(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $user_id  = get_current_user_id();
        $block_id = absint($req['user_id']);
        if (!$block_id || !get_user_by('id', $block_id)) {
            return new WP_Error('invalid_user', 'Invalid user ID', ['status' => 400]);
        }
        if (!class_exists(__NAMESPACE__ . '\\BlockedUsers')) {
            return new WP_Error('missing_feature', 'Blocking not available', ['status' => 500]);
        }
        BlockedUsers::add($user_id, $block_id);
        return rest_ensure_response(['status' => 'blocked', 'user_id' => $block_id]);
    }
}
