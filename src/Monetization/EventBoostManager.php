<?php
namespace ArtPulse\Monetization;

use WP_REST_Request;

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
        register_rest_route('artpulse/v1', '/boost/create-checkout', [
            'methods'  => 'POST',
            'callback' => [self::class, 'create_checkout'],
            'permission_callback' => function () { return is_user_logged_in(); },
        ]);
    }

    public static function create_checkout(WP_REST_Request $req)
    {
        $event = absint($req->get_param('event_id'));
        if (!$event) {
            return new \WP_Error('invalid_event', 'Invalid event', ['status' => 400]);
        }
        // Placeholder: integration with Stripe would go here.
        return rest_ensure_response(['checkout_url' => '#']);
    }
}
