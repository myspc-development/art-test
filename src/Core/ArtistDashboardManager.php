<?php
namespace ArtPulse\Core;

use WP_REST_Request;
use WP_REST_Response;

class ArtistDashboardManager
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/artist/dashboard', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_dashboard_data'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
    }

    public static function get_dashboard_data(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $data = [
            'membership_level'   => get_user_meta($user_id, 'ap_membership_level', true),
            'membership_expires' => get_user_meta($user_id, 'ap_membership_expires', true),
            'artworks'           => [],
        ];

        $artworks = get_posts([
            'post_type'      => 'artpulse_artwork',
            'post_status'    => ['publish','pending','draft'],
            'author'         => $user_id,
            'posts_per_page' => -1,
        ]);

        foreach ($artworks as $art) {
            $data['artworks'][] = [
                'id'    => $art->ID,
                'title' => $art->post_title,
                'link'  => get_edit_post_link($art->ID) ?: get_permalink($art),
            ];
        }

        return rest_ensure_response($data);
    }
}
