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
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/calendar')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/calendar', [
                'methods'  => 'GET',
                'callback' => [self::class, 'get_feed'],
                'permission_callback' => '__return_true',
            ]);
        }
    }

    public static function get_feed(WP_REST_Request $req)
    {
        $lat       = $req->get_param('lat');
        $lng       = $req->get_param('lng');
        $radius    = $req->get_param('radius_km');
        $start     = $req->get_param('start');
        $end       = $req->get_param('end');

        $cache_key = 'ap_cal_' . md5(json_encode([
            round((float) $lat, 2),
            round((float) $lng, 2),
            (float) $radius,
            $start,
            $end,
        ]));

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return rest_ensure_response($cached);
        }

        $events = \ArtPulse\Util\ap_fetch_calendar_events($lat, $lng, $radius, $start, $end);
        set_transient($cache_key, $events, MINUTE_IN_SECONDS * 10);
        return rest_ensure_response($events);
    }
}
