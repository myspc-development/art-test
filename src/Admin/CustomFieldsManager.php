<?php
namespace ArtPulse\Admin;

/**
 * Manages custom RSVP fields.
 */
class CustomFieldsManager
{
    /**
     * Register hooks.
     */
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * REST API routes for retrieving and saving custom field data.
     */
    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/rsvp/custom-fields', [
            'methods'  => ['GET', 'POST'],
            'callback' => [self::class, 'route_handler'],
            'permission_callback' => [self::class, 'check_permission'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function check_permission(): bool
    {
        return is_user_logged_in();
    }

    public static function route_handler(\WP_REST_Request $request)
    {
        // Placeholder only. Real logic would fetch/save fields.
        return rest_ensure_response(['status' => 'custom fields placeholder']);
    }
}
