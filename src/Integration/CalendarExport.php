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
        add_rewrite_rule('^artist/([0-9]+)/events\\.ics$', 'index.php?ap_ics_artist=$matches[1]', 'top');
        add_rewrite_rule('^feeds/ical/artist/([0-9]+)\\.ics$', 'index.php?ap_ics_artist=$matches[1]', 'top');
        add_rewrite_rule('^feeds/ical/gallery/([0-9]+)\\.ics$', 'index.php?ap_ics_org=$matches[1]', 'top');
        add_rewrite_rule('^feeds/ical/all\\.ics$', 'index.php?ap_ics_all=1', 'top');
    }

    public static function register_query_vars(array $vars): array
    {
        $vars[] = 'ap_ics_event';
        $vars[] = 'ap_ics_org';
        $vars[] = 'ap_ics_artist';
        $vars[] = 'ap_ics_all';
        return $vars;
    }

    public static function maybe_output(): void
    {
        $event_id  = get_query_var('ap_ics_event');
        $org_id    = get_query_var('ap_ics_org');
        $artist_id = get_query_var('ap_ics_artist');
        $all_feed  = get_query_var('ap_ics_all');

        if ($event_id) {
            self::output_event(intval($event_id));
        } elseif ($org_id) {
            self::output_org(intval($org_id));
        } elseif ($artist_id) {
            self::output_artist(intval($artist_id));
        } elseif ($all_feed) {
            self::output_all();
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

    private static function output_artist(int $artist_id): void
    {
        $events = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'author'         => $artist_id,
        ]);

        header('Content-Type: text/calendar; charset=utf-8');
        $filename = 'artist-' . $artist_id . '.ics';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo self::build_artist_ics($events);
        exit;
    }

    private static function output_all(): void
    {
        $events = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ]);

        header('Content-Type: text/calendar; charset=utf-8');
        $filename = 'all-events.ics';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo self::build_all_ics($events);
        exit;
    }

    private static function build_event_ics(\WP_Post $event): string
    {
        $uid   = 'event-' . $event->ID . '@' . parse_url(home_url(), PHP_URL_HOST);
        $start = get_post_meta($event->ID, 'event_start_date', true);
        $end   = get_post_meta($event->ID, 'event_end_date', true) ?: $start;
        $ds    = self::format_date($start);
        $de    = self::format_date($end);
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ArtPulse//Event//EN',
            'BEGIN:VTIMEZONE',
            'TZID:' . $ds['tzid'],
            'END:VTIMEZONE',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART;TZID=' . $ds['tzid'] . ':' . $ds['local'],
            'DTSTART:' . $ds['utc'],
            'DTEND;TZID=' . $de['tzid'] . ':' . $de['local'],
            'DTEND:' . $de['utc'],
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
        $tz = wp_timezone_string() ?: 'UTC';
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ArtPulse//Organization//EN',
            'BEGIN:VTIMEZONE',
            'TZID:' . $tz,
            'END:VTIMEZONE',
        ];
        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:event-' . $event->ID . '@' . parse_url(home_url(), PHP_URL_HOST);
            $start   = get_post_meta($event->ID, 'event_start_date', true);
            $end     = get_post_meta($event->ID, 'event_end_date', true) ?: $start;
            $ds      = self::format_date($start);
            $de      = self::format_date($end);
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART;TZID=' . $ds['tzid'] . ':' . $ds['local'];
            $lines[] = 'DTSTART:' . $ds['utc'];
            $lines[] = 'DTEND;TZID=' . $de['tzid'] . ':' . $de['local'];
            $lines[] = 'DTEND:' . $de['utc'];
            $lines[] = 'SUMMARY:' . self::escape($event->post_title);
            $lines[] = 'DESCRIPTION:' . self::escape(wp_strip_all_tags($event->post_content));
            $lines[] = 'LOCATION:' . self::escape(get_post_meta($event->ID, 'venue_name', true));
            $lines[] = 'URL:' . get_permalink($event->ID);
            $lines[] = 'END:VEVENT';
        }
        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    private static function build_artist_ics(array $events): string
    {
        $tz = wp_timezone_string() ?: 'UTC';
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ArtPulse//Artist//EN',
            'BEGIN:VTIMEZONE',
            'TZID:' . $tz,
            'END:VTIMEZONE',
        ];
        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:event-' . $event->ID . '@' . parse_url(home_url(), PHP_URL_HOST);
            $start   = get_post_meta($event->ID, 'event_start_date', true);
            $end     = get_post_meta($event->ID, 'event_end_date', true) ?: $start;
            $ds      = self::format_date($start);
            $de      = self::format_date($end);
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART;TZID=' . $ds['tzid'] . ':' . $ds['local'];
            $lines[] = 'DTSTART:' . $ds['utc'];
            $lines[] = 'DTEND;TZID=' . $de['tzid'] . ':' . $de['local'];
            $lines[] = 'DTEND:' . $de['utc'];
            $lines[] = 'SUMMARY:' . self::escape($event->post_title);
            $lines[] = 'DESCRIPTION:' . self::escape(wp_strip_all_tags($event->post_content));
            $lines[] = 'LOCATION:' . self::escape(get_post_meta($event->ID, 'venue_name', true));
            $lines[] = 'URL:' . get_permalink($event->ID);
            $lines[] = 'END:VEVENT';
        }
        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    private static function build_all_ics(array $events): string
    {
        $tz = wp_timezone_string() ?: 'UTC';
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ArtPulse//AllEvents//EN',
            'BEGIN:VTIMEZONE',
            'TZID:' . $tz,
            'END:VTIMEZONE',
        ];
        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:event-' . $event->ID . '@' . parse_url(home_url(), PHP_URL_HOST);
            $start   = get_post_meta($event->ID, 'event_start_date', true);
            $end     = get_post_meta($event->ID, 'event_end_date', true) ?: $start;
            $ds      = self::format_date($start);
            $de      = self::format_date($end);
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
            $lines[] = 'DTSTART;TZID=' . $ds['tzid'] . ':' . $ds['local'];
            $lines[] = 'DTSTART:' . $ds['utc'];
            $lines[] = 'DTEND;TZID=' . $de['tzid'] . ':' . $de['local'];
            $lines[] = 'DTEND:' . $de['utc'];
            $lines[] = 'SUMMARY:' . self::escape($event->post_title);
            $lines[] = 'DESCRIPTION:' . self::escape(wp_strip_all_tags($event->post_content));
            $lines[] = 'LOCATION:' . self::escape(get_post_meta($event->ID, 'venue_name', true));
            $lines[] = 'URL:' . get_permalink($event->ID);
            $lines[] = 'END:VEVENT';
        }
        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }

    private static function format_date(string $date): array
    {
        $ts   = strtotime($date);
        $tzid = wp_timezone_string() ?: 'UTC';
        return [
            'tzid'  => $tzid,
            'local' => date('Ymd\THis', $ts),
            'utc'   => gmdate('Ymd\THis\Z', $ts),
        ];
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
