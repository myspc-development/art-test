<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use ArtPulse\Traits\Registerable;

class ArtistUpgradeRestController
{
    use Registerable;

    private const HOOKS = [
        'rest_api_init' => 'register_routes',
    ];

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/artist-upgrade', [
            'methods'  => 'POST',
            'callback' => [self::class, 'handle_request'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public static function handle_request(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return new WP_Error('not_logged_in', 'Must be logged in', ['status' => 401]);
        }

        $user = get_userdata($user_id);
        if (!$user || user_can($user, 'artist')) {
            return new WP_Error('already_artist', 'Already an artist', ['status' => 400]);
        }

        $post_id = wp_insert_post([
            'post_type'   => 'ap_artist_request',
            'post_status' => 'pending',
            'post_title'  => 'Artist Upgrade: User ' . $user_id,
            'post_author' => $user_id,
        ], true);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        return rest_ensure_response(['request_id' => $post_id, 'status' => 'pending']);
    }
}
