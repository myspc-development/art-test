<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;
use ArtPulse\Payment\StripeHelper;

class EventBoostManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'maybe_install_table']);
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function install_table(): void
    {
        global $wpdb;
        $table   = $wpdb->prefix . 'ap_event_boosts';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT,
            user_id BIGINT,
            amount DECIMAL(6,2),
            method VARCHAR(20),
            boosted_at DATETIME,
            expires_at DATETIME,
            KEY post_id (post_id)
        ) $charset;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function maybe_install_table(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_boosts';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            self::install_table();
        }
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered('artpulse/v1', '/boost/create-checkout')) {
            register_rest_route('artpulse/v1', '/boost/create-checkout', [
            'methods'  => 'POST',
            'callback' => [self::class, 'create_checkout'],
            'permission_callback' => function () { return is_user_logged_in(); },
        ]);
        }
        if (!ap_rest_route_registered('artpulse/v1', '/boost/webhook')) {
            register_rest_route('artpulse/v1', '/boost/webhook', [
            'methods'  => 'POST',
            'callback' => [self::class, 'handle_webhook'],
            'permission_callback' => '__return_true',
        ]);
        }
    }

    public static function create_checkout(WP_REST_Request $req)
    {
        $event = absint($req->get_param('event_id'));
        if (!$event) {
            return new \WP_Error('invalid_event', 'Invalid event', ['status' => 400]);
        }
        $settings = get_option('artpulse_settings', []);
        $amount   = floatval($settings['boost_price'] ?? 10);
        $currency = $settings['currency'] ?? 'usd';

        $session = StripeHelper::create_session([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'client_reference_id'  => get_current_user_id(),
            'line_items'           => [[
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => intval($amount * 100),
                    'product_data' => ['name' => 'Event Boost'],
                ],
                'quantity' => 1,
            ]],
            'metadata'    => ['event_id' => $event],
            'success_url' => home_url('/?ap_payment=success'),
            'cancel_url'  => home_url('/?ap_payment=cancel'),
        ]);
        if (is_wp_error($session)) {
            return $session;
        }

        return rest_ensure_response(['checkout_url' => $session->url]);
    }

    public static function handle_webhook(WP_REST_Request $req)
    {
        $payload    = $req->get_body();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $settings   = get_option('artpulse_settings', []);
        $secret     = $settings['stripe_webhook_secret'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $secret);
        } catch (\Exception $e) {
            return new \WP_Error('invalid_signature', 'Invalid signature', ['status' => 400]);
        }

        if ($event->type === 'checkout.session.completed') {
            $session   = $event->data->object;
            $event_id  = absint($session->metadata->event_id ?? 0);
            $user_id   = absint($session->client_reference_id ?? 0);
            $amount    = isset($session->amount_total) ? $session->amount_total / 100 : 0;
            if ($event_id && $user_id) {
                self::record_boost($event_id, $user_id, $amount, 'stripe');
            }
        }

        return rest_ensure_response(['received' => true]);
    }

    public static function record_boost(int $event_id, int $user_id, float $amount, string $method): void
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_boosts';
        $wpdb->insert($table, [
            'post_id'    => $event_id,
            'user_id'    => $user_id,
            'amount'     => $amount,
            'method'     => $method,
            'boosted_at' => current_time('mysql'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);
    }

    public static function is_boosted(int $event_id): bool
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_event_boosts';
        $now   = current_time('mysql');
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE post_id = %d AND expires_at > %s", $event_id, $now));
        return $count > 0;
    }
}
