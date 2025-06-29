<?php
namespace ArtPulse\Monetization;

/**
 * Handles promo codes and discounts.
 */
class PromoManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/promo-code/apply', [
            'methods'  => 'POST',
            'callback' => [self::class, 'apply_code'],
            'permission_callback' => '__return_true',
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function apply_code(\WP_REST_Request $req)
    {
        return rest_ensure_response(['status' => 'promo placeholder']);
    }
}
