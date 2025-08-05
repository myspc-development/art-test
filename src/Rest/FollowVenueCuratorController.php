<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;

class FollowVenueCuratorController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        if (!ap_rest_route_registered('artpulse/v1', '/follow/venue')) {
            register_rest_route('artpulse/v1', '/follow/venue', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'follow_venue'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'venue_id' => ['type' => 'integer', 'required' => true],
            ],
        ]);
        }

        if (!ap_rest_route_registered('artpulse/v1', '/followed/venues')) {
            register_rest_route('artpulse/v1', '/followed/venues', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_followed_venues'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
        }

        if (!ap_rest_route_registered('artpulse/v1', '/follow/curator')) {
            register_rest_route('artpulse/v1', '/follow/curator', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'follow_curator'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'curator_id' => ['type' => 'integer', 'required' => true],
            ],
        ]);
        }

        if (!ap_rest_route_registered('artpulse/v1', '/followed/curators')) {
            register_rest_route('artpulse/v1', '/followed/curators', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_followed_curators'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
        }
    }

    public static function follow_venue(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $venue_id = absint($req['venue_id']);
        $list = get_user_meta($user_id, 'ap_following_venues', true);
        $list = is_array($list) ? $list : [];
        if (!in_array($venue_id, $list, true)) {
            $list[] = $venue_id;
            update_user_meta($user_id, 'ap_following_venues', $list);
        }
        return rest_ensure_response(['venues' => array_map('intval', $list)]);
    }

    public static function get_followed_venues(): WP_REST_Response
    {
        $list = get_user_meta(get_current_user_id(), 'ap_following_venues', true);
        $list = is_array($list) ? array_map('intval', $list) : [];
        return rest_ensure_response($list);
    }

    public static function follow_curator(WP_REST_Request $req): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $curator_id = absint($req['curator_id']);
        $list = get_user_meta($user_id, 'ap_following_curators', true);
        $list = is_array($list) ? $list : [];
        if (!in_array($curator_id, $list, true)) {
            $list[] = $curator_id;
            update_user_meta($user_id, 'ap_following_curators', $list);
        }
        return rest_ensure_response(['curators' => array_map('intval', $list)]);
    }

    public static function get_followed_curators(): WP_REST_Response
    {
        $list = get_user_meta(get_current_user_id(), 'ap_following_curators', true);
        $list = is_array($list) ? array_map('intval', $list) : [];
        return rest_ensure_response($list);
    }
}
