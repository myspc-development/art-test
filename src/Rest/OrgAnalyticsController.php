<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class OrgAnalyticsController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        if (!ap_rest_route_registered('artpulse/v1', '/org-metrics')) {
            register_rest_route('artpulse/v1', '/org-metrics', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_metrics'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
        }

        if (!ap_rest_route_registered('artpulse/v1', '/event/(?P<id>\d+)/rsvp-stats')) {
            register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/rsvp-stats', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_event_rsvp_stats'],
            'permission_callback' => [\ArtPulse\Rest\RsvpRestController::class, 'check_permissions'],
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);
        }
    }

    public static function get_metrics(WP_REST_Request $request): WP_REST_Response {
        $user_id = get_current_user_id();
        $org_id  = get_user_meta($user_id, 'ap_organization_id', true);
        if (!$org_id) {
            return rest_ensure_response([]);
        }

        $key  = 'ap_org_metrics_' . $org_id;
        $data = get_transient($key);
        if ($data === false) {
            $event_query = new \WP_Query([
                'post_type'     => 'artpulse_event',
                'post_status'   => ['publish','pending','draft'],
                'fields'        => 'ids',
                'no_found_rows' => true,
                'meta_key'      => '_ap_event_organization',
                'meta_value'    => $org_id,
            ]);
            $event_count = count($event_query->posts);

            $artwork_query = new \WP_Query([
                'post_type'     => 'artpulse_artwork',
                'post_status'   => ['publish','pending','draft'],
                'fields'        => 'ids',
                'no_found_rows' => true,
                'meta_query'    => [
                    [
                        'key'   => 'org_id',
                        'value' => $org_id,
                    ],
                ],
            ]);
            $artwork_count = count($artwork_query->posts);

            $data = [
                'event_count'   => $event_count,
                'artwork_count' => $artwork_count,
            ];
            set_transient($key, $data, MINUTE_IN_SECONDS * 15);
        }

        return rest_ensure_response($data);
    }

    public static function get_event_rsvp_stats(WP_REST_Request $request): WP_REST_Response
    {
        $event_id = absint($request->get_param('id'));
        $history  = get_post_meta($event_id, 'event_rsvp_history', true);
        if (!is_array($history)) {
            $history = [];
        }
        ksort($history);
        $favorites     = (int) get_post_meta($event_id, 'ap_favorite_count', true);
        $waitlist      = get_post_meta($event_id, 'event_waitlist', true);
        $attended      = get_post_meta($event_id, 'event_attended', true);
        $waitlist_ct   = is_array($waitlist) ? count($waitlist) : 0;
        $attended_ct   = is_array($attended) ? count($attended) : 0;
        return rest_ensure_response([
            'dates'       => array_keys($history),
            'counts'      => array_values($history),
            'views'       => (int) get_post_meta($event_id, 'view_count', true),
            'total_rsvps' => array_sum($history),
            'favorites'   => $favorites,
            'waitlist'    => $waitlist_ct,
            'attended'    => $attended_ct,
        ]);
    }
}
