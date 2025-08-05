<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_Error;

class SpotlightAnalyticsController
{
    public static function register(): void
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/spotlight/view', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'log_view'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'id' => [
                    'validate_callback' => 'is_numeric',
                    'required'          => true,
                ],
            ],
        ]);
    }

    public static function log_view(WP_REST_Request $request)
    {
        $id = absint($request['id']);
        if (!$id) {
            return new WP_Error('invalid_id', 'Invalid spotlight ID', ['status' => 400]);
        }
        $views = (int) get_post_meta($id, 'spotlight_views', true);
        update_post_meta($id, 'spotlight_views', $views + 1);
        return rest_ensure_response(['views' => $views + 1]);
    }
}
