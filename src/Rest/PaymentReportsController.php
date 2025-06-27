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
        register_rest_route('artpulse/v1', '/payment-reports', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_reports'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }

    public static function get_reports(WP_REST_Request $request): WP_REST_Response
    {
        $metrics = PaymentAnalyticsDashboard::get_metrics();
        return rest_ensure_response([ 'metrics' => $metrics ]);
    }
}
