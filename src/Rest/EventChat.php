<?php
namespace ArtPulse\Rest;

use WP_Error;
use WP_REST_Request;

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/chat', [
        'methods'             => 'GET',
        'callback'            => __NAMESPACE__ . '\\ap_rest_get_event_chat',
        'permission_callback' => '__return_true', // or add auth logic
    ]);
});

function ap_rest_get_event_chat(WP_REST_Request $request) {
    $event_id = (int) $request['id'];

    // Validate event exists
    if (!get_post($event_id)) {
        return new WP_Error('event_not_found', 'Event not found', ['status' => 404]);
    }

    // TODO: Replace with real chat lookup (e.g., from postmeta or CPT)
    $chat = [
        ['user' => 'Alice', 'msg' => 'Welcome to the event!'],
        ['user' => 'Bob',   'msg' => 'Looking forward to it!'],
    ];

    return rest_ensure_response($chat);
}
