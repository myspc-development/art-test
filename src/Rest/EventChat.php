<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/chat', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\ap_rest_get_event_chat',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ]);
    register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/message', [
        'methods'             => 'POST',
        'callback'            => __NAMESPACE__ . '\\ap_rest_post_event_message',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
        'args'                => [
            'content' => [ 'type' => 'string', 'required' => true ],
        ],
    ]);
});

function ap_rest_get_event_chat(WP_REST_Request $request) {
    $event_id = (int) $request['id'];

    // Validate event exists
    if (!get_post($event_id)) {
        return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat';
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT user_id, content, created_at FROM $table WHERE event_id = %d ORDER BY created_at ASC",
            $event_id
        ),
        ARRAY_A
    );

    $chat = array_map(static function ($row) {
        $user = get_userdata((int) $row['user_id']);
        return [
            'user_id'    => (int) $row['user_id'],
            'author'     => $user ? $user->display_name : '',
            'content'    => $row['content'],
            'created_at' => $row['created_at'],
        ];
    }, $rows);

    return rest_ensure_response($chat);
}

function ap_rest_post_event_message(WP_REST_Request $request) {
    $event_id = (int) $request['id'];
    if (!get_post($event_id)) {
        return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
    }
    $content = sanitize_text_field($request['content']);
    if ($content === '') {
        return new WP_Error('empty_content', 'Message content required', ['status' => 400]);
    }
    global $wpdb;
    $table = $wpdb->prefix . 'ap_event_chat';
    $wpdb->insert($table, [
        'event_id'   => $event_id,
        'user_id'    => get_current_user_id(),
        'content'    => $content,
        'created_at' => current_time('mysql'),
    ]);
    return rest_ensure_response(['status' => 'ok']);
}
