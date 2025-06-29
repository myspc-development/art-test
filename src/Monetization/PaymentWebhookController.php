<?php
namespace ArtPulse\Monetization;

/**
 * Receives payment provider webhooks.
 */
class PaymentWebhookController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/payment/webhook', [
            'methods'  => 'POST',
            'callback' => [self::class, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle(\WP_REST_Request $req)
    {
        return rest_ensure_response(['status' => 'webhook placeholder']);
    }
}
