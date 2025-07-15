<?php
namespace ArtPulse\Community;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class EventVoteRestController {
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/vote', [
            'methods'  => 'POST',
            'callback' => [self::class, 'vote'],
            'permission_callback' => fn() => is_user_logged_in(),
            'args'     => ['id' => ['validate_callback' => 'is_numeric']],
        ]);
        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/votes', [
            'methods'  => 'GET',
            'callback' => [self::class, 'count'],
            'permission_callback' => '__return_true',
            'args'     => ['id' => ['validate_callback' => 'is_numeric']],
        ]);
        register_rest_route('artpulse/v1', '/leaderboards/top-events', [
            'methods' => 'GET',
            'callback' => [self::class, 'top_events'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function vote(WP_REST_Request $req): WP_REST_Response|WP_Error {
        $event_id = absint($req['id']);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', 'Invalid event', ['status' => 404]);
        }
        $user_id = get_current_user_id();
        $count = EventVoteManager::vote($event_id, $user_id);
        return rest_ensure_response(['votes' => $count]);
    }

    public static function count(WP_REST_Request $req): WP_REST_Response|WP_Error {
        $event_id = absint($req['id']);
        if (!$event_id) {
            return new WP_Error('invalid_event', 'Invalid event', ['status' => 404]);
        }
        $count = EventVoteManager::get_votes($event_id);
        $voted = is_user_logged_in() && EventVoteManager::has_voted($event_id, get_current_user_id());
        return rest_ensure_response(['votes' => $count, 'voted' => $voted]);
    }

    public static function top_events(WP_REST_Request $req): WP_REST_Response {
        $limit = $req->get_param('limit') ? absint($req['limit']) : 10;
        $list = EventVoteManager::get_top_voted($limit);
        return rest_ensure_response($list);
    }
}
