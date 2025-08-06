<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class OrgMetaController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'routes']);
    }

    public static function routes(): void
    {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/meta')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/org/(?P<id>\d+)/meta', [
            'methods'             => ['GET', 'POST'],
            'callback'            => [self::class, 'handle'],
            'permission_callback' => function () { return current_user_can('read'); },
            'args'                => [ 'id' => ['validate_callback' => 'is_numeric'] ],
        ]);
        }
    }

    public static function handle(WP_REST_Request $req): WP_REST_Response
    {
        $id = absint($req['id']);
        if ($req->get_method() === 'GET') {
            $data = [
                'logo'   => get_post_meta($id, 'branding_logo', true),
                'color'  => get_post_meta($id, 'branding_color', true),
                'footer' => get_post_meta($id, 'branding_footer', true),
            ];
            return rest_ensure_response($data);
        }
        $body = json_decode($req->get_body(), true) ?: [];
        update_post_meta($id, 'branding_logo', sanitize_text_field($body['logo'] ?? ''));
        update_post_meta($id, 'branding_color', sanitize_hex_color($body['color'] ?? ''));
        update_post_meta($id, 'branding_footer', sanitize_text_field($body['footer'] ?? ''));
        return rest_ensure_response(['success' => true]);
    }
}
