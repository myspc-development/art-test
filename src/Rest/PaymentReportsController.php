<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use ArtPulse\Admin\PaymentAnalyticsDashboard;

class PaymentReportsController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/payment-reports')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/payment-reports', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_reports'],
            'permission_callback' => function () {
                if (!current_user_can('manage_options')) {
                    return new \WP_Error('rest_forbidden', __('Unauthorized.', 'artpulse'), ['status' => 403]);
                }
                return true;
            },
        ]);
        }
    }

    public static function get_reports(WP_REST_Request $request): WP_REST_Response
    {
        $start = sanitize_text_field($request->get_param('start_date') ?? '');
        $end   = sanitize_text_field($request->get_param('end_date') ?? '');

        $metrics = PaymentAnalyticsDashboard::get_metrics($start, $end);

        return rest_ensure_response(['metrics' => $metrics]);
    }
}
