<?php
namespace ArtPulse\AI;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Post;

/**
 * REST controller for GPT-generated artist bio summaries.
 */
class BioSummaryRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/bio-summary/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_summary'],
            'permission_callback' => '__return_true',
            'args'                => [
                'id' => [
                    'type' => 'integer',
                ],
            ],
        ]);
    }

    public static function get_summary(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $post_id = (int) $request->get_param('id');
        $post    = get_post($post_id);
        if (!$post || $post->post_type !== 'artpulse_artist') {
            return new WP_Error('not_found', 'Artist not found', ['status' => 404]);
        }

        $cache_key = 'ap_bio_summary_' . $post_id;
        $summary   = get_transient($cache_key);
        if (!$summary) {
            $opts = get_option('artpulse_settings', []);
            $key  = $opts['openai_api_key'] ?? '';
            if (!$key) {
                return new WP_Error('no_key', 'OpenAI not configured', ['status' => 500]);
            }
            $bio = get_post_meta($post_id, 'artist_bio', true) ?: $post->post_content;
            $bio = wp_strip_all_tags($bio);
            $prompt = 'Summarize the following artist biography in 40 words or less: ' . $bio;
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $key,
                    'Content-Type'  => 'application/json',
                ],
                'body' => wp_json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens'  => 80,
                    'temperature' => 0.7,
                ]),
                'timeout' => 15,
            ]);
            if (is_wp_error($response)) {
                return $response;
            }
            $data    = json_decode(wp_remote_retrieve_body($response), true);
            $summary = trim($data['choices'][0]['message']['content'] ?? '');
            if ($summary) {
                set_transient($cache_key, $summary, DAY_IN_SECONDS);
            }
        }

        return rest_ensure_response(['summary' => $summary]);
    }
}
