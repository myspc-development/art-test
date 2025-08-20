<?php
namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Provides simple event metrics for organization dashboards.
 */
class EventAnalyticsController extends WP_REST_Controller
{
    protected $namespace = ARTPULSE_API_NAMESPACE;

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/analytics/events/summary', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_summary'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
            'args'                => [
                'range' => ['type' => 'string', 'default' => '30d'],
            ],
        ]);
    }

    public function get_summary(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $range   = $request->get_param('range') ?: '30d';
        $days    = intval(rtrim($range, 'd')) ?: 30;
        $after   = date('Y-m-d', strtotime('-' . $days . ' days'));

        $query = new \WP_Query([
            'post_type'      => 'artpulse_event',
            'author'         => $user_id,
            'date_query'     => [ [ 'after' => $after, 'inclusive' => true ] ],
            'post_status'    => ['publish', 'draft', 'pending', 'future'],
            'fields'         => 'ids',
            'nopaging'       => true,
            'no_found_rows'  => true,
        ]);

        $counts = [ 'total' => 0, 'published' => 0, 'draft' => 0 ];
        $ids = $query->posts;
        foreach ($ids as $id) {
            $counts['total']++;
            $status = get_post_status($id);
            if ('publish' === $status) {
                $counts['published']++;
            } elseif ('draft' === $status) {
                $counts['draft']++;
            }
        }

        global $wpdb;
        $rsvps = ['going' => 0, 'waitlist' => 0, 'cancelled' => 0];
        if ($ids) {
            $table = $wpdb->prefix . 'ap_rsvps';
            $in    = implode(',', array_map('intval', $ids));
            $rows  = $wpdb->get_results("SELECT status, COUNT(*) AS c FROM $table WHERE event_id IN ($in) GROUP BY status", ARRAY_A);
            foreach ($rows as $row) {
                $st = $row['status'];
                if (isset($rsvps[$st])) {
                    $rsvps[$st] = intval($row['c']);
                }
            }
        }

        return rest_ensure_response([
            'events' => $counts,
            'rsvps'  => $rsvps,
        ]);
    }
}
