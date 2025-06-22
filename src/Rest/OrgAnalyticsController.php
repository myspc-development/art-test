<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class OrgAnalyticsController {
    public static function register(): void {
        register_rest_route('artpulse/v1', '/org-metrics', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_metrics'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public static function get_metrics(WP_REST_Request $request): WP_REST_Response {
        $user_id = get_current_user_id();
        $org_id  = get_user_meta($user_id, 'ap_organization_id', true);
        if (!$org_id) {
            return rest_ensure_response([]);
        }

        $event_query = new \WP_Query([
            'post_type'     => 'artpulse_event',
            'post_status'   => 'any',
            'fields'        => 'ids',
            'no_found_rows' => true,
            'meta_key'      => '_ap_event_organization',
            'meta_value'    => $org_id,
        ]);
        $event_count = count($event_query->posts);

        $artwork_query = new \WP_Query([
            'post_type'     => 'artpulse_artwork',
            'post_status'   => 'any',
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

        return rest_ensure_response([
            'event_count'   => $event_count,
            'artwork_count' => $artwork_count,
        ]);
    }
}
