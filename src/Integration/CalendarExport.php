<?php
namespace ArtPulse\Integration;

class CalendarExport
{
    public static function register(): void
    {
        add_action('init', [self::class, 'add_rewrite_rules']);
        add_filter('query_vars', [self::class, 'register_query_vars']);
        add_action('template_redirect', [self::class, 'maybe_output']);
    }

    public static function add_rewrite_rules(): void
    {
        add_rewrite_rule('^events/([0-9]+)/export\\.ics$', 'index.php?ap_ics_event=$matches[1]', 'top');
        add_rewrite_rule('^org/([0-9]+)/calendar\\.ics$', 'index.php?ap_ics_org=$matches[1]', 'top');
    }

    public static function register_query_vars(array $vars): array
    {
        $vars[] = 'ap_ics_event';
        $vars[] = 'ap_ics_org';
        return $vars;
    }

    public static function maybe_output(): void
    {
        $event_id = get_query_var('ap_ics_event');
        $org_id   = get_query_var('ap_ics_org');

        if ($event_id) {
            self::output_event(intval($event_id));
        } elseif ($org_id) {
            self::output_org(intval($org_id));
        }
    }

    private static function output_event(int $event_id): void
    {
        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'artpulse_event') {
            status_header(404);
            exit;
        }

        header('Content-Type: text/calendar; charset=utf-8');
        $filename = sanitize_title($event->post_title) . '.ics';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo self::build_event_ics($event);
        exit;
    }

    private static function output_org(int $org_id): void
    {
        $events = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_ap_event_organization',
            'meta_value'     => $org_id,
        ]);

        header('Content-Type: text/calendar; charset=utf-8');
        $filename = 'organization-' . $org_id . '.ics';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo self::build_org_ics($events);
        exit;
    }

    private static function build_event_ics(\WP_Post $event): string
    {
        $uid   = 'event-' . $event->ID . '@' . parse_url(home_url(), PHP_URL_HOST);
        $start = get_post_meta($event->ID, 'event_start_date', true);
        $end   = get_post_meta($event->ID, 'event_end_date', true) ?: $start;
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ArtPulse//Event//EN',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:' . self::format_date($start),
            'DTEND:' . self::format_date($end),
            'SUMMARY:' . self::escape($event->post_title),
            'DESCRIPTION:' . self::escape(wp_strip_all_tags($event->post_content)),
            'LOCATION:' . self::escape(get_post_meta($event->ID, 'venue_name', true)),
            'URL:' . get_permalink($event->ID),
            'END:VEVENT',
            'END:VCALENDAR',
        ];
        return implode("\r\n", $lines);
    }

    private static function build_org_ics(array $events): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ArtPulse//Organization//EN',
        ];
        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:event-' . $event->ID . '@' . parse_url(home_url(), PHP_URL_HOST);
            $start   = get_post_meta($event->ID, 'event_start_date', true);
            $end     = get_post_meta($event->ID, 'event_end_date', true) ?: $start;
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART:' . self::format_date($start);
            $lines[] = 'DTEND:' . self::format_date($end);
            $lines[] = 'SUMMARY:' . self::escape($event->post_title);
            $lines[] = 'DESCRIPTION:' . self::escape(wp_strip_all_tags($event->post_content));
            $lines[] = 'LOCATION:' . self::escape(get_post_meta($event->ID, 'venue_name', true));
            $lines[] = 'URL:' . get_permalink($event->ID);
            $lines[] = 'END:VEVENT';
        }
        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    private static function format_date(string $date): string
    {
        $ts = strtotime($date);
        return gmdate('Ymd\THis\Z', $ts);
    }

    private static function escape(string $val): string
    {
        $val = str_replace('\\', '\\\\', $val);
        $val = str_replace("\n", "\\n", $val);
        $val = str_replace(',', '\\,', $val);
        $val = str_replace(';', '\\;', $val);
        return $val;
    }
}
