<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RsvpRestController
{
    public static function register(): void
    {
        register_rest_route('artpulse/v1', '/rsvp', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'join'],
            'permission_callback' => function () { return is_user_logged_in(); },
            'args'                => [
                'event_id' => [ 'validate_callback' => 'is_numeric' ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/rsvp/cancel', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'cancel'],
            'permission_callback' => function () { return is_user_logged_in(); },
            'args'                => [
                'event_id' => [ 'validate_callback' => 'is_numeric' ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/waitlist/remove', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'remove_waitlist'],
            'permission_callback' => function () { return is_user_logged_in(); },
            'args'                => [
                'event_id' => [ 'validate_callback' => 'is_numeric' ],
            ],
        ]);
    }

    protected static function get_lists(int $event_id): array
    {
        $rsvps    = get_post_meta($event_id, 'event_rsvp_list', true);
        $waitlist = get_post_meta($event_id, 'event_waitlist', true);
        return [
            'rsvps'    => is_array($rsvps) ? $rsvps : [],
            'waitlist' => is_array($waitlist) ? $waitlist : [],
        ];
    }

    protected static function store_lists(int $event_id, array $rsvps, array $waitlist): void
    {
        update_post_meta($event_id, 'event_rsvp_list', array_values($rsvps));
        update_post_meta($event_id, 'event_waitlist', array_values($waitlist));
    }

    protected static function validate_event(int $event_id): bool
    {
        return $event_id && get_post_type($event_id) === 'artpulse_event';
    }

    public static function join(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request->get_param('event_id'));
        if (!self::validate_event($event_id)) {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        $user_id = get_current_user_id();

        ['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists($event_id);

        // Remove from both lists first
        $rsvps    = array_values(array_diff($rsvps, [$user_id]));
        $waitlist = array_values(array_diff($waitlist, [$user_id]));

        $limit = intval(get_post_meta($event_id, 'event_rsvp_limit', true));

        if ($limit && count($rsvps) >= $limit) {
            if (!in_array($user_id, $waitlist, true)) {
                $waitlist[] = $user_id;
            }
        } else {
            if (!in_array($user_id, $rsvps, true)) {
                $rsvps[] = $user_id;
            }
        }

        self::store_lists($event_id, $rsvps, $waitlist);

        return rest_ensure_response([
            'rsvp_count'     => count($rsvps),
            'waitlist_count' => count($waitlist),
        ]);
    }

    public static function cancel(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request->get_param('event_id'));
        if (!self::validate_event($event_id)) {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        $user_id = get_current_user_id();
        ['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists($event_id);

        $rsvps    = array_values(array_diff($rsvps, [$user_id]));
        $waitlist = array_values(array_diff($waitlist, [$user_id]));

        $limit = intval(get_post_meta($event_id, 'event_rsvp_limit', true));
        if (($limit === 0 || count($rsvps) < $limit) && !empty($waitlist)) {
            $promote  = array_shift($waitlist);
            $rsvps[] = $promote;
        }

        self::store_lists($event_id, $rsvps, $waitlist);

        return rest_ensure_response([
            'rsvp_count'     => count($rsvps),
            'waitlist_count' => count($waitlist),
        ]);
    }

    public static function remove_waitlist(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request->get_param('event_id'));
        if (!self::validate_event($event_id)) {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        $user_id = get_current_user_id();
        ['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists($event_id);

        $waitlist = array_values(array_diff($waitlist, [$user_id]));

        self::store_lists($event_id, $rsvps, $waitlist);

        return rest_ensure_response([
            'rsvp_count'     => count($rsvps),
            'waitlist_count' => count($waitlist),
        ]);
    }
}
