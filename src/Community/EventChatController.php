<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventChatController
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_event_chat';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_event_chat';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
            event_id BIGINT NOT NULL,
            user_id BIGINT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY event_id (event_id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }
        dbDelta($sql);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/chat', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'list'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/chat', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'add'],
            'permission_callback' => [self::class, 'can_post'],
            'args'                => [
                'id'      => [ 'validate_callback' => 'is_numeric' ],
                'content' => [ 'type' => 'string', 'required' => true ],
            ],
        ]);
    }

    public static function can_post(WP_REST_Request $req): bool
    {
        if (!is_user_logged_in()) {
            return false;
        }
        $event_id = absint($req['id']);
        $user_id  = get_current_user_id();
        $list = get_post_meta($event_id, 'event_rsvp_list', true);
        return is_array($list) && in_array($user_id, $list, true);
    }

    public static function list(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $event_id = absint($req['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 404]);
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_chat';
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, content, created_at FROM $table WHERE event_id = %d ORDER BY created_at ASC LIMIT 50",
                $event_id
            ),
            ARRAY_A
        );
        $messages = array_map(static function($row) {
            $user   = get_userdata((int) $row['user_id']);
            $avatar = get_avatar_url((int) $row['user_id'], ['size' => 48]);
            if (!$avatar) {
                $avatar = plugins_url('assets/images/default-avatar.png', ARTPULSE_PLUGIN_FILE);
            }
            if ($avatar) {
                $avatar = set_url_scheme($avatar, 'https');
            }
            return [
                'user_id'    => (int) $row['user_id'],
                'author'     => $user ? $user->display_name : '',
                'avatar'     => $avatar,
                'content'    => $row['content'],
                'created_at' => $row['created_at'],
            ];
        }, $rows);
        return rest_ensure_response($messages);
    }

    public static function add(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $event_id = absint($req['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 404]);
        }
        $content = sanitize_text_field($req['content']);
        if ($content === '') {
            return new WP_Error('empty_content', 'Message content required.', ['status' => 400]);
        }
        $user_id = get_current_user_id();
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_chat';
        $wpdb->insert($table, [
            'event_id'   => $event_id,
            'user_id'    => $user_id,
            'content'    => $content,
            'created_at' => current_time('mysql'),
        ]);
        return rest_ensure_response(['status' => 'ok']);
    }
}
