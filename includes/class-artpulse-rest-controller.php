<?php
if (!defined('ABSPATH')) { exit; }

use WP_REST_Request;

/**
 * Registers placeholder REST API routes used by dashboard widgets.
 *
 * TODO: Replace mocked data with real queries per ArtPulse_Member_Dashboard_Roadmap.md.
 */
class ArtPulse_REST_Controller {
    public static function register() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('artpulse/v1', '/events/nearby', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_nearby_events'],
            'permission_callback' => '__return_true',
            'args' => [
                'lat' => [ 'type' => 'number', 'required' => true ],
                'lng' => [ 'type' => 'number', 'required' => true ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\\d+)/rsvp', [
            'methods'  => 'POST',
            'callback' => [self::class, 'rsvp_event'],
            'permission_callback' => function() { return is_user_logged_in(); },
            'args' => [ 'id' => [ 'type' => 'integer', 'required' => true ] ],
        ]);

        register_rest_route('artpulse/v1', '/user/(?P<id>\\d+)/follow', [
            'methods'  => 'POST',
            'callback' => [self::class, 'follow_user'],
            'permission_callback' => function() { return is_user_logged_in(); },
            'args' => [ 'id' => [ 'type' => 'integer', 'required' => true ] ],
        ]);
    }

    public static function get_nearby_events(WP_REST_Request $request) {
        // TODO: implement real nearby events query
        return rest_ensure_response([
            [ 'id' => 1, 'title' => 'Sample Event', 'distance' => 1.2 ]
        ]);
    }

    public static function rsvp_event(WP_REST_Request $request) {
        $id = absint($request['id']);
        // TODO: store RSVP in database
        return rest_ensure_response([ 'event_id' => $id, 'status' => 'rsvped' ]);
    }

    public static function follow_user(WP_REST_Request $request) {
        $id = absint($request['id']);
        // TODO: store follow relationship
        return rest_ensure_response([ 'user_id' => $id, 'status' => 'following' ]);
    }
}

ArtPulse_REST_Controller::register();
