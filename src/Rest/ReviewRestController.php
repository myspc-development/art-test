<?php
namespace ArtPulse\Rest;

use ArtPulse\Community\ReviewManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ReviewRestController
{
    public static function register(): void
    {
        register_rest_route('artpulse/v1', '/reviews', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'add_review'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => self::get_schema(),
        ]);

        register_rest_route('artpulse/v1', '/reviews', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_reviews'],
            'permission_callback' => '__return_true',
            'args'                => [
                'target_id' => [
                    'type'     => 'integer',
                    'required' => true,
                ],
                'target_type' => [
                    'type'     => 'string',
                    'required' => true,
                ],
            ],
        ]);
    }

    private static function get_schema(): array
    {
        return [
            'target_id' => [
                'type'        => 'integer',
                'required'    => true,
            ],
            'target_type' => [
                'type'        => 'string',
                'required'    => true,
            ],
            'rating' => [
                'type'        => 'integer',
                'required'    => true,
                'minimum'     => 1,
                'maximum'     => 5,
            ],
            'text' => [
                'type'        => 'string',
                'required'    => false,
            ],
        ];
    }

    public static function add_review(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user = get_current_user_id();
        $target = absint($request['target_id']);
        $type = sanitize_key($request['target_type']);
        $rating = intval($request['rating']);
        $text = sanitize_textarea_field($request['text'] ?? '');

        if (!$target || !$type || $rating < 1 || $rating > 5) {
            return new WP_Error('invalid_params', 'Invalid parameters.', ['status' => 400]);
        }

        ReviewManager::add_review($user, $target, $type, $rating, $text);

        return rest_ensure_response(['success' => true]);
    }

    public static function get_reviews(WP_REST_Request $request): WP_REST_Response
    {
        $target = absint($request['target_id']);
        $type = sanitize_key($request['target_type']);
        $reviews = ReviewManager::get_reviews($target, $type, 20);
        $avg = ReviewManager::get_average_rating($target, $type);
        return rest_ensure_response([
            'average' => $avg,
            'reviews' => $reviews,
        ]);
    }
}
