<?php
use WP_Post;
use WP_REST_Request;

if (!defined('ABSPATH')) {
    exit;
}

add_action('save_post', 'ap_auto_tagger_handle_save', 20, 3);
add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/bio-summary', [
        'methods'             => 'POST',
        'callback'            => 'ap_generate_bio_summary',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args'                => [
            'post_id' => [ 'type' => 'integer', 'required' => true ],
        ],
    ]);
});

function ap_auto_tagger_get_key(): string
{
    $opts = get_option('artpulse_settings', []);
    return isset($opts['openai_key']) ? trim($opts['openai_key']) : '';
}

function ap_auto_tagger_handle_save(int $post_id, WP_Post $post, bool $update): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }

    $key = ap_auto_tagger_get_key();
    if (!$key) {
        return;
    }

    $tax_objs = get_object_taxonomies($post->post_type, 'objects');
    $target_tax = null;
    foreach ($tax_objs as $tax) {
        if (!$tax->hierarchical) {
            $target_tax = $tax->name;
            break;
        }
    }
    if (!$target_tax) {
        return;
    }

    $content = wp_strip_all_tags($post->post_title . "\n" . $post->post_content);

    $resp = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode([
            'model'    => 'gpt-3.5-turbo',
            'messages' => [
                [ 'role' => 'system', 'content' => 'Return a comma separated list of up to 5 short tags.' ],
                [ 'role' => 'user',   'content' => $content ],
            ],
            'max_tokens' => 40,
        ]),
        'timeout' => 15,
    ]);

    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        return;
    }

    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $text = trim($data['choices'][0]['message']['content'] ?? '');
    if ($text === '') {
        return;
    }

    $tags = array_filter(array_map('trim', explode(',', $text)));
    if ($tags) {
        wp_set_object_terms($post_id, $tags, $target_tax, true);
    }
}

function ap_generate_bio_summary(WP_REST_Request $req)
{
    $key = ap_auto_tagger_get_key();
    if (!$key) {
        return new WP_Error('no_key', 'OpenAI key missing', [ 'status' => 500 ]);
    }
    $post_id = intval($req['post_id']);
    $post    = get_post($post_id);
    if (!$post || $post->post_type !== 'artpulse_artist') {
        return new WP_Error('invalid_post', 'Invalid artist', [ 'status' => 404 ]);
    }
    if (!current_user_can('edit_post', $post_id)) {
        return new WP_Error('forbidden', 'Insufficient permissions', [ 'status' => 403 ]);
    }

    $content = wp_strip_all_tags($post->post_content);

    $resp = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode([
            'model'    => 'gpt-3.5-turbo',
            'messages' => [
                [ 'role' => 'system', 'content' => 'Summarize this artist bio in two sentences.' ],
                [ 'role' => 'user',   'content' => $content ],
            ],
            'max_tokens' => 120,
        ]),
        'timeout' => 15,
    ]);

    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        return new WP_Error('api_error', 'API request failed', [ 'status' => 500 ]);
    }

    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $summary = trim($data['choices'][0]['message']['content'] ?? '');
    return [ 'summary' => $summary ];
}
