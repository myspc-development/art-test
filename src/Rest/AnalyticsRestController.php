<?php
namespace ArtPulse\Rest;

use ArtPulse\Core\EventMetrics;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AnalyticsRestController
{
    public static function register(): void
    {
        register_rest_route('artpulse/v1', '/analytics/trends', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'get_trends'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                => [
                'event_id' => [ 'type' => 'integer', 'required' => true ],
                'days'     => [ 'type' => 'integer', 'default' => 30 ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/analytics/export', [
            'methods'             => 'GET',
            'callback'            => [self::class, 'export_csv'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args'                => [
                'event_id' => [ 'type' => 'integer', 'required' => true ],
                'days'     => [ 'type' => 'integer', 'default' => 30 ],
            ],
        ]);
    }

    public static function get_trends(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $event_id = absint($request['event_id']);
        $days     = max(1, absint($request['days']));
        if (!$event_id) {
            return new WP_Error('invalid_event', 'Invalid event.', ['status' => 400]);
        }

        $views  = EventMetrics::get_counts($event_id, 'view', $days);
        $favs   = EventMetrics::get_counts($event_id, 'favorite', $days);
        $tickets = self::get_ticket_counts($event_id, $days);

        return rest_ensure_response([
            'days'      => $views['days'],
            'views'     => $views['counts'],
            'favorites' => $favs['counts'],
            'tickets'   => $tickets['counts'],
        ]);
    }

    private static function get_ticket_counts(int $event_id, int $days): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_tickets';
        $since = date('Y-m-d', strtotime('-' . $days . ' days'));
        $rows  = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(purchase_date) AS day, COUNT(id) AS c FROM $table WHERE event_id = %d AND status = 'active' AND purchase_date >= %s GROUP BY day",
                $event_id,
                $since
            )
        );
        $output = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime('-' . $i . ' days'));
            $output[$d] = 0;
        }
        foreach ($rows as $row) {
            $output[$row->day] = (int) $row->c;
        }
        return [ 'days' => array_keys($output), 'counts' => array_values($output) ];
    }

    public static function export_csv(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $data = self::get_trends($request);
        if ($data instanceof WP_Error) {
            return $data;
        }
        $data = $data->get_data();
        $stream = fopen('php://temp', 'w');
        fputcsv($stream, ['day', 'views', 'favorites', 'tickets']);
        foreach ($data['days'] as $i => $day) {
            fputcsv($stream, [$day, $data['views'][$i], $data['favorites'][$i], $data['tickets'][$i]]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return new WP_REST_Response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics.csv"',
        ]);
    }
}
