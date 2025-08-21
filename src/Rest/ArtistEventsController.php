<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use function ArtPulse\Rest\Util\require_login_and_cap;

class ArtistEventsController
{
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
        register_rest_route(
            'artpulse/v1',
            '/artist-events',
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_events'],
                'permission_callback' => require_login_and_cap(static function () {
                    return current_user_can('read');
                }),
            ]
        );
    }

    public static function get_events(WP_REST_Request $request)
    {
        $uid = get_current_user_id();

        $posts = get_posts([
            'post_type'   => 'artpulse_event',
            'author'      => $uid,
            'post_status' => ['publish', 'pending', 'draft', 'future'],
            'numberposts' => -1,
        ]);

        $events = [];
        foreach ($posts as $post) {
            $status = $post->post_status;
            $color  = '#6b7280';
            switch ($status) {
                case 'draft':
                    $color = '#9ca3af';
                    break;
                case 'future':
                    $color = '#fbbf24';
                    break;
                case 'publish':
                    $color = '#3b82f6';
                    break;
            }
            $events[] = [
                'id'     => $post->ID,
                'title'  => $post->post_title,
                'start'  => get_post_meta($post->ID, '_ap_event_date', true),
                'end'    => get_post_meta($post->ID, 'event_end_date', true),
                'status' => $status,
                'color'  => $color,
                'edit'   => get_edit_post_link($post->ID),
            ];
        }

        return rest_ensure_response($events);
    }
}
