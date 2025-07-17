<?php
namespace ArtPulse\Integration;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Handles organization webhooks for external automations.
 */
class WebhookManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('init', [self::class, 'maybe_install_tables']);
        add_action('artpulse_ticket_purchased', [self::class, 'handle_ticket_purchased'], 10, 4);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/org/(?P<id>\\d+)/webhooks', [
            'methods'  => 'GET',
            'callback' => [self::class, 'list_webhooks'],
            'permission_callback' => [self::class, 'check_manage_org'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
        register_rest_route('artpulse/v1', '/org/(?P<id>\\d+)/webhooks', [
            'methods'  => 'POST',
            'callback' => [self::class, 'create_webhook'],
            'permission_callback' => [self::class, 'check_manage_org'],
            'args' => ['id' => ['validate_callback' => 'absint']],
        ]);
        register_rest_route('artpulse/v1', '/org/(?P<id>\\d+)/webhooks/(?P<hid>\\d+)', [
            'methods'  => 'PUT',
            'callback' => [self::class, 'update_webhook'],
            'permission_callback' => [self::class, 'check_manage_org'],
            'args' => [
                'id'  => ['validate_callback' => 'absint'],
                'hid' => ['validate_callback' => 'absint'],
            ],
        ]);
        register_rest_route('artpulse/v1', '/org/(?P<id>\\d+)/webhooks/(?P<hid>\\d+)', [
            'methods'  => 'DELETE',
            'callback' => [self::class, 'delete_webhook'],
            'permission_callback' => [self::class, 'check_manage_org'],
            'args' => [
                'id'  => ['validate_callback' => 'absint'],
                'hid' => ['validate_callback' => 'absint'],
            ],
        ]);
    }

    public static function check_manage_org(): bool
    {
        return current_user_can('manage_options');
    }

    public static function maybe_install_tables(): void
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'ap_webhooks';
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists !== $table) {
            $charset = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            PRIMARY KEY (id),
                org_id BIGINT NOT NULL,
                url VARCHAR(255) NOT NULL,
                events VARCHAR(255) NOT NULL,
                secret VARCHAR(64) NOT NULL,
                active TINYINT(1) NOT NULL DEFAULT 1,
                last_status VARCHAR(20) DEFAULT NULL,
                last_sent DATETIME DEFAULT NULL,
                KEY org_id (org_id)
            ) $charset;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        $log_table  = $wpdb->prefix . 'ap_webhook_logs';
        $exists_log = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $log_table));
        if ($exists_log !== $log_table) {
            $charset = $wpdb->get_charset_collate();
            $sql2 = "CREATE TABLE $log_table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (id),
                subscription_id BIGINT NOT NULL,
                status_code VARCHAR(20) DEFAULT NULL,
                response_body TEXT NULL,
                timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY sub_id (subscription_id)
            ) $charset;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql2);
        }
    }

    public static function list_webhooks(WP_REST_Request $req): WP_REST_Response
    {
        $org_id = absint($req['id']);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhooks';
        $rows  = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE org_id = %d", $org_id), ARRAY_A);
        return rest_ensure_response($rows);
    }

    public static function create_webhook(WP_REST_Request $req)
    {
        $org_id = absint($req['id']);
        $url    = esc_url_raw($req->get_param('url'));
        $events = (array) $req->get_param('events');
        $active = intval($req->get_param('active')) ? 1 : 0;
        if (!$url || empty($events)) {
            return new WP_Error('invalid', 'Invalid parameters.', ['status' => 400]);
        }
        $secret = wp_generate_password(32, false, false);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhooks';
        $wpdb->insert($table, [
            'org_id' => $org_id,
            'url'    => $url,
            'events' => implode(',', array_map('sanitize_text_field', $events)),
            'secret' => $secret,
            'active' => $active,
        ]);
        return rest_ensure_response(['id' => $wpdb->insert_id, 'secret' => $secret]);
    }

    public static function update_webhook(WP_REST_Request $req)
    {
        $org_id = absint($req['id']);
        $id     = absint($req['hid']);
        $data   = [];
        if ($req->has_param('url')) {
            $data['url'] = esc_url_raw($req->get_param('url'));
        }
        if ($req->has_param('events')) {
            $data['events'] = implode(',', array_map('sanitize_text_field', (array) $req->get_param('events')));
        }
        if ($req->has_param('active')) {
            $data['active'] = intval($req->get_param('active')) ? 1 : 0;
        }
        if (!$data) {
            return new WP_Error('invalid', 'No updates provided.', ['status' => 400]);
        }
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhooks';
        $wpdb->update($table, $data, ['id' => $id, 'org_id' => $org_id]);
        return rest_ensure_response(['updated' => true]);
    }

    public static function delete_webhook(WP_REST_Request $req)
    {
        $org_id = absint($req['id']);
        $id     = absint($req['hid']);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhooks';
        $wpdb->delete($table, ['id' => $id, 'org_id' => $org_id]);
        return rest_ensure_response(['deleted' => true]);
    }

    public static function handle_ticket_purchased(int $user_id, int $event_id, int $ticket_id, int $qty): void
    {
        $data = [
            'ticket_id' => $ticket_id,
            'event_id'  => $event_id,
            'buyer_id'  => $user_id,
            'quantity'  => $qty,
        ];
        $org_id = 0;
        self::trigger_event('ticket_sold', $org_id, $data);
    }

    public static function trigger_event(string $event, int $org_id, array $data): void
    {
        if (function_exists('ap_trigger_webhooks')) {
            ap_trigger_webhooks($event, $org_id, $data);
        }
    }

    private static function send_webhook(object $webhook, string $event, array $data): void
    {
        $payload = [
            'event'     => $event,
            'timestamp' => current_time('mysql'),
            'data'      => $data,
        ];
        $json = wp_json_encode($payload);
        $sig  = hash_hmac('sha256', $json, $webhook->secret);
        $res  = wp_remote_post($webhook->url, [
            'body'    => $json,
            'headers' => [
                'Content-Type'       => 'application/json',
                'X-ArtPulse-Signature' => 'sha256=' . $sig,
            ],
            'timeout' => 5,
        ]);
        $status = is_wp_error($res) ? 'error' : (string) wp_remote_retrieve_response_code($res);
        global $wpdb;
        $table = $wpdb->prefix . 'ap_webhooks';
        $wpdb->update($table, [
            'last_status' => $status,
            'last_sent'   => current_time('mysql'),
        ], ['id' => $webhook->id]);
    }
}
