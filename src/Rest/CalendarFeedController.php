<?php
namespace ArtPulse\Rest;

use WP_REST_Request;

class CalendarFeedController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/calendar', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_feed'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
        ]);
    }

    public static function get_feed(WP_REST_Request $req)
    {
        $lat = $req->get_param('lat');
        $lng = $req->get_param('lng');

        return rest_ensure_response(\ArtPulse\Util\ap_fetch_calendar_events($lat, $lng));
    }
}
