<?php
namespace ArtPulse\Admin;

class DashboardLayoutEndpoint
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/dashboard-layout/(?P<context>\\w+)')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/dashboard-layout/(?P<context>\\w+)', [
            'methods'  => ['GET', 'POST'],
            'callback' => [self::class, 'handle'],
            'permission_callback' => fn () => current_user_can('manage_options'),
        ]);
        }
    }

    public static function handle(\WP_REST_Request $request)
    {
        $ctx = sanitize_key($request['context']);
        $option = get_option('ap_dashboard_widget_config', []);

        if ($request->get_method() === 'GET') {
            $layout = $option[$ctx] ?? [];
            return rest_ensure_response($layout);
        }

        $layout = $request->get_json_params();
        if (!is_array($layout)) {
            return new \WP_Error('invalid', 'Invalid layout', ['status' => 400]);
        }
        $option[$ctx] = $layout;
        update_option('ap_dashboard_widget_config', $option);
        return rest_ensure_response(['saved' => true]);
    }
}
