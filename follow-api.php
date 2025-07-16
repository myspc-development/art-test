<?php
use ArtPulse\Community\ActivityFeed;

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/follow/feed', [
        'methods'             => 'GET',
        'callback'            => function (WP_REST_Request $request) {
            $limit = max(1, intval($request->get_param('limit') ?: 20));
            $items = ActivityFeed::get_feed(get_current_user_id(), $limit);
            return rest_ensure_response($items);
        },
        'permission_callback' => function () {
            if (!current_user_can('read')) {
                return new WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
            }
            return true;
        },
        'args'                => [ 'limit' => [ 'type' => 'integer', 'default' => 20 ] ],
    ]);
});
