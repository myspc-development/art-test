<?php
namespace ArtPulse\AI;

use WP_Error;

/**
 * Helper for interacting with the OpenAI API.
 */
class OpenAIClient
{
    /**
     * Generate tags from text using OpenAI.
     *
     * @param string $text
     * @return array<string>|WP_Error
     */
    public static function generateTags(string $text): array|string|WP_Error
    {
        $prompt = 'Generate relevant tags for this art description. Return tags as a comma-separated list.';
        $response = self::request([
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $prompt . ' ' . $text],
        ], 30);

        if (is_wp_error($response)) {
            return $response;
        }

        $tags = array_filter(array_map('trim', explode(',', strip_tags($response))));
        return array_values($tags);
    }

    /**
     * Generate a short summary of the given bio.
     *
     * @param string $bio
     * @return string|WP_Error
     */
    public static function generateSummary(string $bio): string|WP_Error
    {
        $prompt = 'Summarize this artist bio.';
        return self::request([
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $prompt . ' ' . $bio],
        ], 60);
    }

    /**
     * Send a chat completion request to OpenAI.
     *
     * @param array $messages
     * @param int   $max_tokens
     * @return string|WP_Error
     */
    private static function request(array $messages, int $max_tokens)
    {
        $key = self::get_api_key();
        if (!$key) {
            return new WP_Error('missing_api_key', __('OpenAI API key not configured.', 'artpulse'), ['status' => 500]);
        }

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode([
                'model'       => 'gpt-3.5-turbo',
                'messages'    => $messages,
                'max_tokens'  => $max_tokens,
                'temperature' => 0.7,
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            self::log_error('Request failed: ' . $response->get_error_message());
            return new WP_Error('openai_request_failed', __('Failed to contact OpenAI.', 'artpulse'), ['status' => 500]);
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 429) {
            self::log_error('Rate limit or server error: ' . $code);
            return new WP_Error('openai_rate_limited', __('OpenAI rate limit reached.', 'artpulse'), ['status' => 429]);
        }
        if ($code < 200 || $code >= 300) {
            self::log_error('HTTP error: ' . $code);
            return new WP_Error('openai_http_error', __('OpenAI API error.', 'artpulse'), ['status' => $code]);
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!isset($data['choices'][0]['message']['content'])) {
            self::log_error('Invalid response: ' . wp_remote_retrieve_body($response));
            return new WP_Error('openai_invalid_response', __('Invalid OpenAI response.', 'artpulse'), ['status' => 500]);
        }

        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * Retrieve the API key from options or constant.
     */
    private static function get_api_key(): string
    {
        $key = get_option('openai_api_key');
        if (!$key && defined('OPENAI_API_KEY')) {
            $key = constant('OPENAI_API_KEY');
        }
        return is_string($key) ? trim($key) : '';
    }

    /**
     * Log an error message to uploads directory.
     */
    private static function log_error(string $message): void
    {
        $uploads = wp_upload_dir();
        $file    = trailingslashit($uploads['basedir']) . 'artpulse-openai.log';
        $entry   = '[' . date('Y-m-d H:i:s') . "] " . $message . "\n";
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log($entry, 3, $file);
    }
}
