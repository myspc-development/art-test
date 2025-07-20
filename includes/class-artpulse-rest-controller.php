<?php
if (!defined('ABSPATH')) { exit; }

use WP_REST_Request;

/**
 * Registers REST API routes used by dashboard widgets.
 */
class ArtPulse_REST_Controller {
    public static function register() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('artpulse/v1', '/events/nearby', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_nearby_events'],
            'permission_callback' => '__return_true',
            'args'                => [
                'lat'    => [ 'type' => 'number', 'required' => true ],
                'lng'    => [ 'type' => 'number', 'required' => true ],
                'radius' => [ 'type' => 'number', 'required' => false, 'default' => 50 ],
                'limit'  => [ 'type' => 'integer', 'required' => false, 'default' => 20 ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/rsvp', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rsvp_event'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [ 'id' => [ 'type' => 'integer', 'required' => true ] ],
        ]);

        register_rest_route('artpulse/v1', '/user/(?P<id>\\d+)/follow', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'follow_user'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [ 'id' => [ 'type' => 'integer', 'required' => true ] ],
        ]);
    }

    public static function get_nearby_events(WP_REST_Request $request) {
        $lat    = floatval($request['lat']);
        $lng    = floatval($request['lng']);
        $radius = floatval($request->get_param('radius'));
        $limit  = absint($request->get_param('limit'));
        if ($radius <= 0) {
            $radius = 50;
        }
        if ($limit <= 0) {
            $limit = 20;
        }

        $query = new WP_Query([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                ['key' => 'event_lat', 'compare' => 'EXISTS'],
                ['key' => 'event_lng', 'compare' => 'EXISTS'],
            ],
        ]);

        $events = [];
        foreach ($query->posts as $post) {
            $ev_lat = get_post_meta($post->ID, 'event_lat', true);
            $ev_lng = get_post_meta($post->ID, 'event_lng', true);
            if ($ev_lat === '' || $ev_lng === '') {
                continue;
            }
            $dist = self::haversine_distance($lat, $lng, (float) $ev_lat, (float) $ev_lng);
            if ($dist <= $radius) {
                $events[] = [
                    'id'       => $post->ID,
                    'title'    => $post->post_title,
                    'distance' => round($dist, 2),
                    'link'     => get_permalink($post),
                ];
            }
        }
        usort($events, static fn($a, $b) => $a['distance'] <=> $b['distance']);
        $events = array_slice($events, 0, $limit);

        return rest_ensure_response($events);
    }

    public static function rsvp_event(WP_REST_Request $request) {
        $id = absint($request['id']);
        if (!$id || get_post_type($id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }
        $user_id = get_current_user_id();
        $events  = get_user_meta($user_id, 'ap_rsvp_events', true);
        if (!is_array($events)) {
            $events = [];
        }
        if (!in_array($id, $events, true)) {
            $events[] = $id;
            update_user_meta($user_id, 'ap_rsvp_events', $events);
        }

        return rest_ensure_response([ 'event_id' => $id, 'status' => 'rsvped' ]);
    }

    public static function follow_user(WP_REST_Request $request) {
        $id = absint($request['id']);
        $user = get_user_by('id', $id);
        if (!$user) {
            return new WP_Error('invalid_user', 'Invalid user.', ['status' => 404]);
        }
        $current  = get_current_user_id();
        $following = get_user_meta($current, 'ap_following', true);
        if (!is_array($following)) {
            $following = [];
        }
        if (!in_array($id, $following, true)) {
            $following[] = $id;
            update_user_meta($current, 'ap_following', $following);
        }

        return rest_ensure_response([ 'user_id' => $id, 'status' => 'following' ]);
    }

    private static function haversine_distance(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $earth = 6371; // km
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lng2 - $lng1);
        $a     = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c     = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth * $c;
    }
}

ArtPulse_REST_Controller::register();
