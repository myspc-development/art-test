<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\ProfileMetrics;
use WP_REST_Request;
use WP_REST_Response;

class ProfileMetricsController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/profile-metrics/(?P<id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_metrics'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                => [
                'metric' => [
                    'type'    => 'string',
                    'default' => 'view',
                ],
                'days' => [
                    'type'    => 'integer',
                    'default' => 30,
                ],
            ],
        ]);
    }

    public static function get_metrics(WP_REST_Request $request): WP_REST_Response
    {
        $profile_id = absint($request->get_param('id'));
        $metric     = sanitize_key($request->get_param('metric'));
        $days       = absint($request->get_param('days'));
        $data       = ProfileMetrics::get_counts($profile_id, $metric, $days);
        $data = apply_filters('ap_profile_metrics_response', $data, $profile_id, $metric, $days);
        return rest_ensure_response($data);
    }
}
