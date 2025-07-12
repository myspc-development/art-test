<?php
namespace ArtPulse\AI;

use WP_Post;

/**
 * Automatically tag posts using the OpenAI API.
 */
class AutoTagger
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('save_post', [self::class, 'maybe_tag'], 20, 3);
    }

    /**
     * Analyze post content and add suggested tags.
     */
    public static function maybe_tag(int $post_id, WP_Post $post, bool $update): void
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        $types = ['artpulse_event', 'artpulse_artist', 'post'];
        if (!in_array($post->post_type, $types, true)) {
            return;
        }

        $opts = get_option('artpulse_settings', []);
        $key  = $opts['openai_api_key'] ?? '';
        if (!$key) {
            return;
        }

        $content = trim(wp_strip_all_tags($post->post_content));
        if (!$content) {
            return;
        }

        $prompt = 'Suggest three short tags for this content as a comma separated list: ' . $content;

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
                'max_tokens'  => 20,
                'temperature' => 0.5,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $text = $data['choices'][0]['message']['content'] ?? '';
        if (!$text) {
            return;
        }

        $tags = array_filter(array_map('trim', explode(',', strip_tags($text))));
        if ($tags) {
            wp_add_post_tags($post_id, $tags);
        }
    }
}
