<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class ImportTemplateController
{
    private const OPTION = 'ap_import_templates';

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
        if (!ap_rest_route_registered('artpulse/v1', '/import-template/(?P<post_type>[^/]+)')) {
            register_rest_route('artpulse/v1', '/import-template/(?P<post_type>[^/]+)', [
                [
                    'methods'             => 'GET',
                    'callback'            => [self::class, 'get_template'],
                    'permission_callback' => [ImportRestController::class, 'check_permissions'],
                    'args'                => [
                        'post_type' => [
                            'validate_callback' => 'sanitize_key',
                        ],
                    ],
                ],
                [
                    'methods'             => 'POST',
                    'callback'            => [self::class, 'save_template'],
                    'permission_callback' => [ImportRestController::class, 'check_permissions'],
                ],
            ]);
        }
    }

    public static function get_template(WP_REST_Request $request): WP_REST_Response
    {
        $post_type = sanitize_key($request['post_type']);
        $templates = get_option(self::OPTION, []);
        return rest_ensure_response($templates[$post_type] ?? new \stdClass());
    }

    public static function save_template(WP_REST_Request $request): WP_REST_Response
    {
        $post_type = sanitize_key($request['post_type']);
        $params    = $request->get_json_params();
        $mapping   = $params['mapping'] ?? [];
        $trim      = !empty($params['trim']);

        if (!is_array($mapping)) {
            $mapping = [];
        }

        $templates            = get_option(self::OPTION, []);
        $templates[$post_type] = [
            'mapping' => $mapping,
            'trim'    => $trim,
        ];
        update_option(self::OPTION, $templates);

        return rest_ensure_response(['success' => true]);
    }
}
