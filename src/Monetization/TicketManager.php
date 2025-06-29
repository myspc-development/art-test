<?php
namespace ArtPulse\Monetization;

/**
 * Manages paid tickets and tiers.
 */
class TicketManager
{
    /**
     * Register actions.
     */
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    /**
     * REST endpoints for ticket operations.
     */
    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/tickets', [
            'methods'  => 'GET',
            'callback' => [self::class, 'list_tickets'],
            'permission_callback' => '__return_true',
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/buy-ticket', [
            'methods'  => 'POST',
            'callback' => [self::class, 'buy_ticket'],
            'permission_callback' => [self::class, 'check_logged_in'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
    }

    public static function check_logged_in(): bool
    {
        return is_user_logged_in();
    }

    public static function list_tickets(\WP_REST_Request $req)
    {
        return rest_ensure_response(['status' => 'tickets placeholder']);
    }

    public static function buy_ticket(\WP_REST_Request $req)
    {
        return rest_ensure_response(['status' => 'purchase placeholder']);
    }
}
