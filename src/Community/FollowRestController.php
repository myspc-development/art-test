<?php

namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class FollowRestController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/follows', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'add_follow'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => self::get_schema(),
        ]);

        register_rest_route('artpulse/v1', '/follows', [
            'methods'             => 'DELETE',
            'callback'            => [self::class, 'remove_follow'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => self::get_schema(),
        ]);

        register_rest_route('artpulse/v1', '/follows', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'list_follows'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'post_type' => [
                    'type'     => 'string',
                    'required' => false,
                    'enum'     => ['artpulse_artist', 'artpulse_event', 'artpulse_org', 'user'],
                ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/followers/(?P<user_id>\\d+)', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_followers'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [
                'user_id' => [ 'type' => 'integer', 'required' => true ],
            ],
        ]);
    }

    public static function get_schema(): array
    {
        return [
            'post_id' => [
                'type'        => 'integer',
                'required'    => true,
                'description' => 'ID of the post to follow or unfollow.',
            ],
            'post_type' => [
                'type'        => 'string',
                'required'    => true,
                'enum'        => ['artpulse_artist', 'artpulse_event', 'artpulse_org', 'user'],
                'description' => 'The post type being followed.',
            ],
        ];
    }

    public static function add_follow(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id   = get_current_user_id();
        $post_id   = absint($request['post_id']);
        $post_type = sanitize_key($request['post_type']);

        if ($post_type === 'user') {
            if (!get_user_by('id', $post_id)) {
                return new WP_Error('invalid_post', 'User not found.', ['status' => 404]);
            }
        } else {
            if (!get_post($post_id)) {
                return new WP_Error('invalid_post', 'Post not found.', ['status' => 404]);
            }
        }

        $follows = get_user_meta($user_id, '_ap_follows', true) ?: [];
        if (!in_array($post_id, $follows, true)) {
            $follows[] = $post_id;
            update_user_meta($user_id, '_ap_follows', $follows);
        }

        FollowManager::add_follow($user_id, $post_id, $post_type);

        return rest_ensure_response(['status' => 'following', 'follows' => $follows]);
    }

    public static function remove_follow(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $user_id   = get_current_user_id();
        $post_id   = absint($request['post_id']);
        $post_type = sanitize_key($request['post_type']);

        if ($post_type === 'user') {
            if (!get_user_by('id', $post_id)) {
                return new WP_Error('invalid_post', 'User not found.', ['status' => 404]);
            }
        } else {
            if (!get_post($post_id)) {
                return new WP_Error('invalid_post', 'Post not found.', ['status' => 404]);
            }
        }

        $follows = get_user_meta($user_id, '_ap_follows', true) ?: [];
        if (($key = array_search($post_id, $follows)) !== false) {
            unset($follows[$key]);
            update_user_meta($user_id, '_ap_follows', array_values($follows));
        }

        FollowManager::remove_follow($user_id, $post_id, $post_type);

        return rest_ensure_response(['status' => 'unfollowed', 'follows' => $follows]);
    }

    public static function list_follows(WP_REST_Request $request): WP_REST_Response
    {
        $user_id   = get_current_user_id();
        $type      = $request['post_type'] ? sanitize_key($request['post_type']) : null;

        $rows = FollowManager::get_user_follows($user_id, $type);
        return rest_ensure_response($rows);
    }

    public static function get_followers(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = absint($request['user_id']);
        $followers = FollowManager::get_followers($user_id);
        return rest_ensure_response(['user_id' => $user_id, 'followers' => $followers]);
    }
}
