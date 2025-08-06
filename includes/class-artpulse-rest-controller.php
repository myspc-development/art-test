<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Registers REST API routes used by dashboard widgets.
 */
class ArtPulse_REST_Controller {
    public static function register() {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes() {
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/rsvp')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/event/(?P<id>\\d+)/rsvp', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'rsvp_event'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [ 'id' => [ 'type' => 'integer', 'required' => true ] ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/user/(?P<id>\\d+)/follow')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/user/(?P<id>\\d+)/follow', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'follow_user'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'                => [ 'id' => [ 'type' => 'integer', 'required' => true ] ],
        ]);
        }
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

}

ArtPulse_REST_Controller::register();
