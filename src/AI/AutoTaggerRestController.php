<?php
namespace ArtPulse\AI;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST controller for applying tags to posts on demand.
 */
class AutoTaggerRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/tag', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'tag_post'],
            'permission_callback' => fn() => current_user_can('edit_posts'),
            'args'                => [
                'post_id' => [
                    'required'          => true,
                    'validate_callback' => 'is_numeric',
                ],
            ],
        ]);
    }

    public static function tag_post(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $post_id = (int) $request->get_param('post_id');

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'post') {
            return new WP_REST_Response(['error' => 'Invalid post ID.'], 404);
        }

        $tags = ['abstract', 'modern', 'colorful'];

        wp_set_post_tags($post_id, $tags, true);

        return rest_ensure_response([
            'success'      => true,
            'post_id'      => $post_id,
            'tags_applied' => $tags,
        ]);
    }
}
