<?php
namespace ArtPulse\Rest;

use ArtPulse\Curator\CuratorManager;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class ProfileVerificationController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/profile/(?P<id>\d+)/verify', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [self::class, 'verify_profile'],
            'permission_callback' => fn() => current_user_can('manage_options'),
            'args'                => [
                'id' => [
                    'validate_callback' => 'is_numeric',
                ],
            ],
        ]);
    }

    public static function verify_profile(WP_REST_Request $req): WP_REST_Response
    {
        $id   = (int) $req['id'];
        $post = get_post($id);
        if ($post && in_array($post->post_type, ['artpulse_artist', 'artpulse_org'], true)) {
            update_post_meta($post->ID, '_ap_is_verified', 1);
            return rest_ensure_response(['success' => true, 'type' => $post->post_type]);
        }

        $curator = CuratorManager::get_by_id($id);
        if ($curator) {
            CuratorManager::verify($id);
            return rest_ensure_response(['success' => true, 'type' => 'curator']);
        }

        return new WP_REST_Response(['message' => 'Profile not found'], 404);
    }
}
