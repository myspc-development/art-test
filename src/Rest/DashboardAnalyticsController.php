<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\DashboardAnalyticsLogger;

class DashboardAnalyticsController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-analytics')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-analytics', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'log_event'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        }
    }

    public static function check_permissions(WP_REST_Request $request): bool
    {
        $nonce = $request->get_header('X-WP-Nonce');
        return is_user_logged_in() && $nonce && wp_verify_nonce($nonce, 'wp_rest');
    }

    public static function log_event(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event   = sanitize_text_field($request['event'] ?? '');
        $details = sanitize_text_field($request['details'] ?? '');
        if ($event === '') {
            return new WP_Error('invalid_event', 'Event required', ['status' => 400]);
        }
        DashboardAnalyticsLogger::log(get_current_user_id(), $event, $details);
        return rest_ensure_response(['logged' => true]);
    }
}
