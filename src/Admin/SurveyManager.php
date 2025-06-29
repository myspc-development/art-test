<?php
namespace ArtPulse\Admin;

/**
 * Handles post-event surveys and responses.
 */
class SurveyManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/survey', [
            'methods'  => ['GET', 'POST'],
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'check_permission'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function check_permission(): bool
    {
        return is_user_logged_in();
    }

    public static function handle(\WP_REST_Request $request)
    {
        return rest_ensure_response(['status' => 'survey placeholder']);
    }
}
