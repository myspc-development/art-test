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
        $provider = sanitize_text_field($req->get_param('provider'));
        $status   = sanitize_text_field($req->get_param('status'));
        $ticket_id = absint($req->get_param('ticket_id'));
        $user_id   = absint($req->get_param('user_id'));

        if ($status !== 'success' || !$ticket_id || !$user_id) {
            return rest_ensure_response(['ignored' => true]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ap_tickets';
        $wpdb->update($table, ['status' => 'active'], ['id' => $ticket_id, 'user_id' => $user_id]);

        do_action('artpulse_ticket_purchased', $user_id, 0, 0, 1);
        return rest_ensure_response(['status' => 'ok']);
    }
}
