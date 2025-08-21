<?php
namespace ArtPulse\Rest;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Rest\Util\Auth;

final class ProfileMetricsController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'routes']);
    }
    public static function routes(): void {
        register_rest_route('ap/v1', '/profile/metrics', [
            'methods'  => WP_REST_Server::READABLE,
            'callback' => [self::class, 'get_metrics'],
            'permission_callback' => Auth::require_login_and_cap(static fn() => current_user_can('read')),
        ]);
    }
    public static function get_metrics(WP_REST_Request $req): WP_REST_Response {
        $uid = get_current_user_id();
        // Count authored events last 30 days
        $q = new \WP_Query([
            'post_type'   => 'artpulse_event',
            'author'      => $uid,
            'date_query'  => [[ 'after' => date('Y-m-d', strtotime('-30 days')), 'inclusive' => true ]],
            'post_status' => ['publish','draft','pending','future'],
            'fields'      => 'ids',
            'no_found_rows' => true,
        ]);
        $events = is_array($q->posts) ? count($q->posts) : 0;

        global $wpdb;
        $rsvps = 0;
        try {
            $table = $wpdb->prefix . 'ap_rsvps';
            $rsvps = (int) $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND created_at >= %s",
                    $uid, date('Y-m-d', strtotime('-30 days')) . ' 00:00:00')
            );
        } catch (\Throwable $e) {}

        return new WP_REST_Response(['recent_events' => $events, 'recent_rsvps' => $rsvps], 200);
    }
}
