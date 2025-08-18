<?php
if (!defined('ABSPATH')) {
    exit;
}

function artpulse_enqueue_widget_scripts(): void {
    if (!is_singular()) {
        return;
    }

    global $post;
    $post = get_post();
    if (!$post) {
        return;
    }

    $has_widget = has_shortcode($post->post_content, 'ap_widget')
        || has_shortcode($post->post_content, 'ap_user_dashboard')
        || has_shortcode($post->post_content, 'ap_react_dashboard')
        || has_shortcode($post->post_content, 'user_dashboard');
    if (!$has_widget) {
        return;
    }

    wp_enqueue_script(
        'art-widgets',
        plugins_url('assets/js/widgets.bundle.js', __FILE__),
        ['wp-element', 'wp-api-fetch'],
        '1.0.0',
        true
    );

    wp_localize_script('art-widgets', 'APChat', [
        'apiRoot'  => esc_url_raw(rest_url()),
        'nonce'    => wp_create_nonce('wp_rest'),
        'loggedIn' => is_user_logged_in(),
    ]);
}

add_action('wp_enqueue_scripts', 'artpulse_enqueue_widget_scripts');
