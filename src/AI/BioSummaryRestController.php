<?php
namespace ArtPulse\AI;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\AI\OpenAIClient;

/**
 * REST controller for generating artist bio summaries using OpenAI.
 */
class BioSummaryRestController
{
    /**
     * Register the controller routes.
     */
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Register REST routes.
     */
    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/bio-summary', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'generate_summary'],
            'permission_callback' => static fn() => current_user_can('edit_posts'),
            'args'                => [
                'bio' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'type'              => 'string',
                ],
            ],
        ]);
    }

    /**
     * Generate a summary from the provided bio using OpenAI.
     */
    public static function generate_summary(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $bio = sanitize_textarea_field($request->get_param('bio'));
        if ($bio === '') {
            return new WP_Error('invalid_bio', __('Invalid bio.', 'artpulse'), ['status' => 400]);
        }

        $summary = OpenAIClient::generateSummary($bio);
        if (is_wp_error($summary)) {
            return $summary;
        }

        return rest_ensure_response(['summary' => $summary]);
    }
}
