<?php
namespace ArtPulse\Admin;

/**
 * Filters and exports user segments.
 */
class SegmentationManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/admin/users', [
            'methods'  => 'GET',
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'check_permission'],
        ]);
    }

    public static function check_permission(): bool
    {
        return current_user_can('manage_options');
    }

    public static function handle(\WP_REST_Request $request)
    {
        return rest_ensure_response(['status' => 'segment placeholder']);
    }
}
