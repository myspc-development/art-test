<?php
namespace ArtPulse\AI;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST controller for generating tags from text via mocked AI.
 */
class AutoTaggerRestController
{
    /**
     * Hook into rest_api_init to register the route.
     */
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * Register REST routes for the controller.
     */
    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/tag', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'generate_tags'],
            'permission_callback' => static fn() => current_user_can('edit_posts'),
            'args'                => [
                'text' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'type'              => 'string',
                ],
            ],
        ]);
    }

    /**
     * Generate tags for provided text (mocked implementation).
     */
    public static function generate_tags(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $text = sanitize_textarea_field($request->get_param('text'));
        if ($text === '') {
            return new WP_Error('invalid_text', __('Invalid text.', 'artpulse'), ['status' => 400]);
        }

        // Mocked AI response.
        $tags = ['abstract', 'modern'];

        return rest_ensure_response($tags);
    }
}
