<?php
namespace ArtPulse\Monetization;

/**
 * Provides membership level utilities.
 */
class MembershipManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/user/membership', [
            'methods'  => ['GET', 'POST'],
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'check_logged_in'],
        ]);
    }

    public static function check_logged_in(): bool
    {
        return is_user_logged_in();
    }

    public static function handle(\WP_REST_Request $req)
    {
        return rest_ensure_response(['status' => 'membership placeholder']);
    }
}
