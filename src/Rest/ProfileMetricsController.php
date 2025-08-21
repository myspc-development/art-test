<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function ArtPulse\Rest\Util\require_login_and_cap;

/**
 * Provides basic profile metrics for dashboards.
 */
class ProfileMetricsController {
    public static function register(): void {
        $c = new self();
        add_action('rest_api_init', [$c, 'register_routes']);
    }

    public function register_routes(): void {
        register_rest_route(ARTPULSE_API_NAMESPACE, '/profile/metrics', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_metrics'],
            'permission_callback' => require_login_and_cap(),
        ]);
    }

    public function get_metrics(WP_REST_Request $request): WP_REST_Response {
        $uid = get_current_user_id();
        $now = current_time('mysql');
        $week_ago = gmdate('Y-m-d H:i:s', strtotime('-7 days', strtotime($now)));

        $events = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => ['publish', 'draft', 'pending', 'future'],
            'author'         => $uid,
            'date_query'     => [ ['after' => $week_ago] ],
            'fields'         => 'ids',
            'nopaging'       => true,
            'no_found_rows'  => true,
        ]);
        $event_ids = $events ? array_map('intval', $events) : [];

        $rsvp_count = 0;
        if ($event_ids) {
            global $wpdb;
            $table = $wpdb->prefix . 'ap_rsvps';
            $in    = implode(',', $event_ids);
            $statuses = "'going','confirmed','attending','waitlist'";
            $rsvp_count = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table} WHERE event_id IN ($in) AND status IN ($statuses) AND created_at >= %s",
                    $week_ago
                )
            );
        }

        $data = [
            'events_created_last_7d' => count($event_ids),
            'rsvps_received_last_7d' => $rsvp_count,
        ];

        return rest_ensure_response($data);
    }
}
