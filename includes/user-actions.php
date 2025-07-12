<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Community\FollowManager;

add_action('wp_ajax_ap_follow_post', 'ap_follow_post');
add_action('wp_ajax_nopriv_ap_follow_post', 'ap_follow_post');
add_action('wp_ajax_ap_unfollow_post', 'ap_unfollow_post');
add_action('wp_ajax_nopriv_ap_unfollow_post', 'ap_unfollow_post');

function ap_follow_post(): void {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Login required', 'artpulse')], 403);
    }
    $post_id   = absint($_POST['post_id'] ?? 0);
    $post_type = sanitize_key($_POST['post_type'] ?? '');
    if (!$post_id || !$post_type) {
        wp_send_json_error(['message' => __('Invalid data', 'artpulse')]);
    }
    FollowManager::add_follow(get_current_user_id(), $post_id, $post_type);
    wp_send_json_success(['status' => 'followed']);
}

function ap_unfollow_post(): void {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Login required', 'artpulse')], 403);
    }
    $post_id   = absint($_POST['post_id'] ?? 0);
    $post_type = sanitize_key($_POST['post_type'] ?? '');
    if (!$post_id || !$post_type) {
        wp_send_json_error(['message' => __('Invalid data', 'artpulse')]);
    }
    FollowManager::remove_follow(get_current_user_id(), $post_id, $post_type);
    wp_send_json_success(['status' => 'unfollowed']);
}
