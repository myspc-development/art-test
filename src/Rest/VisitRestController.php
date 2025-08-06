<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\VisitTracker;

class VisitRestController
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
        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/checkin')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/checkin', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'checkin'],
            'permission_callback' => function () {
                return current_user_can('read');
            },
            'args'                => [
                'event_id'   => [ 'validate_callback' => 'is_numeric', 'required' => true ],
                'institution'=> [ 'sanitize_callback' => 'sanitize_text_field' ],
                'group_size' => [ 'validate_callback' => 'is_numeric' ],
            ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/visits')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/visits', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'list'],
            'permission_callback' => [\ArtPulse\Rest\RsvpRestController::class, 'check_permissions'],
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);
        }

        if (!ap_rest_route_registered(ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/visits/export')) {
            register_rest_route(ARTPULSE_API_NAMESPACE, '/event/(?P<id>\d+)/visits/export', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'export'],
            'permission_callback' => [\ArtPulse\Rest\RsvpRestController::class, 'check_permissions'],
            'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
        ]);
        }
    }

    public static function checkin(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $event_id = absint($req->get_param('event_id'));
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            return new WP_Error('invalid_event', __('Invalid event.', 'artpulse'), ['status' => 400]);
        }
        $institution = sanitize_text_field($req->get_param('institution') ?: '');
        $group_size  = absint($req->get_param('group_size') ?: 1);
        $user_id     = get_current_user_id();
        VisitTracker::record($event_id, $user_id, $institution, $group_size);
        return rest_ensure_response(['success' => true]);
    }

    public static function list(WP_REST_Request $req): WP_REST_Response
    {
        $id = absint($req['id']);
        $rows = VisitTracker::get_visits($id);
        return rest_ensure_response($rows);
    }

    public static function export(WP_REST_Request $req): WP_REST_Response
    {
        $id   = absint($req['id']);
        $rows = VisitTracker::get_visits($id);
        $stream = fopen('php://temp', 'w');
        fputcsv($stream, ['institution','group_size','user_id','visit_date']);
        foreach ($rows as $row) {
            fputcsv($stream, [
                $row['institution'],
                $row['group_size'],
                $row['user_id'],
                $row['visit_date'],
            ]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return new WP_REST_Response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="visits.csv"',
        ]);
    }
}
