<?php
use ArtPulse\Messages\AP_Message;

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/messages/send', [
        'methods'  => 'POST',
        'callback' => function ($req) {
            $sender  = get_current_user_id();
            $receiver = absint($req['receiver_id']);
            $content  = sanitize_textarea_field($req['content']);

            if (!$receiver || empty($content)) {
                return new WP_Error('invalid', 'Invalid message', ['status' => 403]);
            }

            $id = AP_Message::send($sender, $receiver, $content);
            return rest_ensure_response(['message_id' => $id]);
        },
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ]);

    register_rest_route('artpulse/v1', '/messages/inbox', [
        'methods'  => 'GET',
        'callback' => function () {
            return rest_ensure_response(AP_Message::get_inbox(get_current_user_id()));
        },
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ]);

    register_rest_route('artpulse/v1', '/messages/read/(?P<id>\d+)', [
        'methods'  => 'POST',
        'callback' => function ($req) {
            AP_Message::mark_read((int)$req['id'], get_current_user_id());
            return rest_ensure_response(['status' => 'read']);
        },
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ]);
});
