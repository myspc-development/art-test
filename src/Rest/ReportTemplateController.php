<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class ReportTemplateController
{
    private const OPTION = 'ap_report_templates';

    public static function register(): void
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered('artpulse/v1', '/report-template/(?P<type>[^/]+)')) {
            register_rest_route('artpulse/v1', '/report-template/(?P<type>[^/]+)', [
                [
                    'methods'             => 'GET',
                    'callback'            => [self::class, 'get_template'],
                    'permission_callback' => [self::class, 'permission'],
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [self::class, 'save_template'],
                    'permission_callback' => [self::class, 'permission'],
                ],
            ]);
        }
    }

    public static function permission(): bool
    {
        return current_user_can('manage_options');
    }

    public static function get_template(WP_REST_Request $request): WP_REST_Response
    {
        $type = sanitize_key($request['type']);
        $templates = get_option(self::OPTION, []);
        return rest_ensure_response($templates[$type] ?? new \stdClass());
    }

    public static function save_template(WP_REST_Request $request): WP_REST_Response
    {
        $type = sanitize_key($request['type']);
        $params = $request->get_json_params();
        $tpl = $params['template'] ?? [];
        if (!is_array($tpl)) {
            $tpl = [];
        }
        $templates = get_option(self::OPTION, []);
        $templates[$type] = $tpl;
        update_option(self::OPTION, $templates);
        return rest_ensure_response(['success' => true]);
    }
}
