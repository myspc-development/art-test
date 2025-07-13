<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/artist', [
        'methods'             => 'GET',
        'callback'            => 'ap_get_artist_overview',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ]);
});

function ap_get_artist_overview() {
    $user_id   = get_current_user_id();
    $followers = (int) get_user_meta($user_id, 'ap_follower_count', true);
    $sales     = (int) get_user_meta($user_id, 'ap_total_sales', true);
    $artworks  = count_user_posts($user_id, 'artpulse_artwork');

    return [
        'followers' => $followers,
        'sales'     => $sales,
        'artworks'  => $artworks,
    ];
}
