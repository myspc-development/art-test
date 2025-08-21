<?php
namespace ArtPulse\Rest;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ArtPulse\Rest\Util\Auth;

/**
 * Provides simple event metrics for organization dashboards.
 */
class EventAnalyticsController extends WP_REST_Controller
{
    // Ensure tests hit the exact namespace they expect.
    protected $namespace = 'ap/v1';

    public static function register(): void
    {
        $controller = new self();
        add_action('rest_api_init', [$controller, 'register_routes']);
        add_action('ap_rsvp_changed', [self::class, 'invalidate_cache'], 10, 1);
    }

    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/analytics/events/summary', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_summary'],
            // Unauthenticated -> 401, authenticated but unauthorized -> 403
            'permission_callback' => Auth::require_login_and_cap(static fn() => current_user_can('read')),
            'args'                => [
                // Tests validate timezone and range clamping; accept either range or explicit dates
                'range' => ['type' => 'string', 'default' => '30d'],
                'start' => ['type' => 'string'],
                'end'   => ['type' => 'string'],
            ],
        ]);
    }

    public function get_summary(WP_REST_Request $request): WP_REST_Response
    {
        $user_id = get_current_user_id();
        $start   = $request->get_param('start');
        $end     = $request->get_param('end');
        $range   = $request->get_param('range') ?: '30d';

        $tz      = wp_timezone();
        $tz_name = wp_timezone_string() ?: 'UTC';
        $utc_tz  = new \DateTimeZone('UTC');

        if ($start && $end) {
            $start_dt  = new \DateTime($start, $tz);
            $end_dt    = new \DateTime($end, $tz);
            $diff_days = $start_dt->diff($end_dt)->days;
            if ($start_dt > $end_dt || $diff_days > 365) {
                return new \WP_Error('invalid_range', __('Invalid range', 'artpulse'), ['status' => 400]);
            }
        } else {
            $days   = intval(rtrim($range, 'd')) ?: 30;
            $days   = max(1, min(365, $days));
            $end_dt = new \DateTime('now', $tz);
            $start_dt = (clone $end_dt)->modify('-' . $days . ' days');
        }

        $after     = $start_dt->format('Y-m-d');
        $until     = $end_dt->format('Y-m-d');
        $after_utc = (clone $start_dt)->setTimezone($utc_tz)->format('Y-m-d');
        $until_utc = (clone $end_dt)->setTimezone($utc_tz)->format('Y-m-d');

        $cache_key = self::cache_key($user_id, $start, $end, $range);
        if (false !== ($cached = get_transient($cache_key))) {
            return rest_ensure_response($cached);
        }

        // Events authored by this user inside range
        $q = new \WP_Query([
            'post_type'     => 'artpulse_event',
            'author'        => $user_id,
            'date_query'    => [[ 'after' => $after, 'inclusive' => true ]],
            'post_status'   => ['publish', 'draft', 'pending', 'future'],
            'fields'        => 'ids',
            'nopaging'      => true,
            'no_found_rows' => true,
        ]);
        $ids = $q->posts;

        $counts = ['total' => 0, 'published' => 0, 'draft' => 0];
        foreach ($ids as $id) {
            $counts['total']++;
            $st = get_post_status($id);
            if ($st === 'publish') $counts['published']++;
            elseif ($st === 'draft') $counts['draft']++;
        }

        global $wpdb;
        $rsvps = ['going' => 0, 'waitlist' => 0, 'cancelled' => 0];
        $unique = 0;
        $total_rsvps = 0;

        if ($ids) {
            $table = $wpdb->prefix . 'ap_rsvps';
            $in    = implode(',', array_map('intval', $ids));

            // Totals by status
            $rows = $wpdb->get_results("SELECT status, COUNT(*) c FROM {$table} WHERE event_id IN ($in) GROUP BY status", ARRAY_A);
            foreach ($rows as $row) {
                $st = $row['status'];
                if (isset($rsvps[$st])) {
                    $c = (int) $row['c'];
                    $rsvps[$st] = $c;
                    $total_rsvps += $c;
                }
            }

            // Unique attendees (by email)
            $unique = (int) $wpdb->get_var("SELECT COUNT(DISTINCT email) FROM {$table} WHERE event_id IN ($in)");

            // Trend in site timezone
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT DATE(CONVERT_TZ(created_at, '+00:00', %s)) d, COUNT(*) c
                     FROM {$table}
                     WHERE event_id IN ($in)
                       AND created_at BETWEEN %s AND %s
                     GROUP BY d
                     ORDER BY d",
                    $tz_name,
                    $after_utc . ' 00:00:00',
                    $until_utc . ' 23:59:59'
                ),
                ARRAY_A
            );
            $trend = array_map(fn($r) => ['date' => $r['d'], 'count' => (int)$r['c']], $rows);

            // Top events
            $tops = $wpdb->get_results("SELECT event_id, COUNT(*) c FROM {$table} WHERE event_id IN ($in) GROUP BY event_id ORDER BY c DESC LIMIT 5", ARRAY_A);
            $top_events = array_map(fn($r) => ['title' => get_the_title((int)$r['event_id']), 'count' => (int)$r['c']], $tops);
            $top_event  = $top_events[0]['title'] ?? '';
        } else {
            $trend = [];
            $top_events = [];
            $top_event = '';
        }

        $confirmed_percent = $total_rsvps ? round(($rsvps['going'] / $total_rsvps) * 100, 2) : 0;

        $data = [
            'events'            => $counts,
            'rsvps'             => $rsvps,
            'total_rsvps'       => $total_rsvps,
            'unique_attendees'  => $unique,
            'confirmed_percent' => $confirmed_percent,
            'trend'             => $trend,
            'top_event'         => $top_event,
            'top_events'        => $top_events,
            'timezone'          => $tz_name,
        ];

        set_transient($cache_key, $data, 10 * MINUTE_IN_SECONDS);
        return rest_ensure_response($data);
    }

    protected static function cache_key(int $user_id, ?string $start, ?string $end, string $range): string
    {
        $key = ($start && $end) ? ($start . ':' . $end) : $range;
        return 'ap_evt_summary_' . $user_id . '_' . md5($key);
    }

    public static function invalidate_cache(int $event_id): void
    {
        $user_id = (int) get_post_field('post_author', $event_id);
        foreach (['7d','30d','90d'] as $r) {
            delete_transient(self::cache_key($user_id, null, null, $r));
        }
    }
}
