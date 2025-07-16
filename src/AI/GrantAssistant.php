<?php
namespace ArtPulse\AI;

use WP_REST_Request;

class GrantAssistant
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/ai/generate-grant-copy', [
            'methods'  => 'POST',
            'callback' => [self::class, 'generate'],
            'permission_callback' => function () { return is_user_logged_in(); },
        ]);
    }

    public static function generate(WP_REST_Request $req)
    {
        $text = $req->get_param('text');
        return rest_ensure_response(['draft' => $text]);
    }
}
