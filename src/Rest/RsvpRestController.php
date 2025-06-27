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

        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/attendees', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_attendees'],
            'permission_callback' => [self::class, 'check_permissions'],
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<id>\d+)/attendees/export', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'export_attendees'],
            'permission_callback' => [self::class, 'check_permissions'],
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/attended', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'toggle_attended'],
            'permission_callback' => [self::class, 'check_permissions'],
            'args'                => [
                'event_id' => [ 'validate_callback' => 'is_numeric' ],
                'user_id'  => [ 'validate_callback' => 'is_numeric' ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/event/(?P<event_id>\d+)/attendees/(?P<user_id>\d+)/remove', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'remove_attendee'],
            'permission_callback' => [self::class, 'check_permissions'],
            'args'                => [
                'event_id' => [ 'validate_callback' => 'is_numeric' ],
                'user_id'  => [ 'validate_callback' => 'is_numeric' ],
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

    public static function check_permissions(WP_REST_Request $request): bool
    {
        $event_id = absint($request->get_param('id') ?: $request->get_param('event_id'));
        if (!$event_id) {
            return false;
        }
        $user_id  = get_current_user_id();
        $user_org = intval(get_user_meta($user_id, 'ap_organization_id', true));
        $event_org = intval(get_post_meta($event_id, '_ap_event_organization', true));
        return $user_org && $event_org && $user_org === $event_org && current_user_can('view_artpulse_dashboard');
    }

    public static function get_attendees(WP_REST_Request $request): WP_REST_Response
    {
        $event_id = absint($request->get_param('id'));
        ['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists($event_id);
        $attended = get_post_meta($event_id, 'event_attended', true);
        if (!is_array($attended)) {
            $attended = [];
        }

        $attendees = [];
        foreach ($rsvps as $uid) {
            $user = get_userdata($uid);
            if (!$user) {
                continue;
            }
            $attendees[] = [
                'ID'     => $uid,
                'email'  => $user->user_email,
                'status' => in_array($uid, $attended, true) ? 'Attended' : 'RSVP',
            ];
        }

        $wl = [];
        foreach ($waitlist as $uid) {
            $user = get_userdata($uid);
            if (!$user) {
                continue;
            }
            $wl[] = [
                'ID'     => $uid,
                'email'  => $user->user_email,
                'status' => 'Waitlist',
            ];
        }

        return rest_ensure_response([
            'attendees' => $attendees,
            'waitlist'  => $wl,
        ]);
    }

    public static function export_attendees(WP_REST_Request $request): WP_REST_Response
    {
        $event_id = absint($request->get_param('id'));
        $date     = get_post_meta($event_id, '_ap_event_date', true);
        $data     = self::get_attendees($request)->get_data();

        $rows = array_merge($data['attendees'], $data['waitlist']);
        $stream = fopen('php://temp', 'w');
        fputcsv($stream, ['user_id', 'email', 'status', 'date']);
        foreach ($rows as $row) {
            fputcsv($stream, [$row['ID'], $row['email'], $row['status'], $date]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return new WP_REST_Response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendees.csv"',
        ]);
    }

    public static function toggle_attended(WP_REST_Request $request): WP_REST_Response
    {
        $event_id = absint($request->get_param('event_id'));
        $user_id  = absint($request->get_param('user_id'));
        $attended = get_post_meta($event_id, 'event_attended', true);
        if (!is_array($attended)) {
            $attended = [];
        }
        if (in_array($user_id, $attended, true)) {
            $attended = array_values(array_diff($attended, [$user_id]));
            $status = false;
        } else {
            $attended[] = $user_id;
            $status = true;
        }
        update_post_meta($event_id, 'event_attended', $attended);
        return rest_ensure_response(['attended' => $status]);
    }

    public static function remove_attendee(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request->get_param('event_id'));
        $user_id  = absint($request->get_param('user_id'));
        if (!self::validate_event($event_id)) {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        ['rsvps' => $rsvps, 'waitlist' => $waitlist] = self::get_lists($event_id);
        $attended = get_post_meta($event_id, 'event_attended', true);
        if (!is_array($attended)) {
            $attended = [];
        }

        $rsvps    = array_values(array_diff($rsvps, [$user_id]));
        $waitlist = array_values(array_diff($waitlist, [$user_id]));
        $attended = array_values(array_diff($attended, [$user_id]));

        $limit = intval(get_post_meta($event_id, 'event_rsvp_limit', true));
        if (($limit === 0 || count($rsvps) < $limit) && !empty($waitlist)) {
            $promote = array_shift($waitlist);
            $rsvps[] = $promote;
        }

        update_post_meta($event_id, 'event_attended', $attended);
        self::store_lists($event_id, $rsvps, $waitlist);

        return rest_ensure_response([
            'rsvp_count'     => count($rsvps),
            'waitlist_count' => count($waitlist),
        ]);
    }
}
