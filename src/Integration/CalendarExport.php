<?php
namespace ArtPulse\Integration;

use ArtPulse\Core\FeedAccessLogger;

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
        add_rewrite_rule('^feeds/events\\.json$', 'index.php?ap_json_feed=1', 'top');
        add_rewrite_rule('^feeds/events\\.xml$', 'index.php?ap_rss_feed=1', 'top');
    }

    public static function register_query_vars(array $vars): array
    {
        $vars[] = 'ap_ics_event';
        $vars[] = 'ap_ics_org';
        $vars[] = 'ap_ics_artist';
        $vars[] = 'ap_ics_all';
        $vars[] = 'ap_json_feed';
        $vars[] = 'ap_rss_feed';
        return $vars;
    }

    public static function maybe_output(): void
    {
        $event_id  = get_query_var('ap_ics_event');
        $org_id    = get_query_var('ap_ics_org');
        $artist_id = get_query_var('ap_ics_artist');
        $all_feed  = get_query_var('ap_ics_all');
        $json_feed = get_query_var('ap_json_feed');
        $rss_feed  = get_query_var('ap_rss_feed');

        if ($event_id) {
            self::output_event(intval($event_id));
        } elseif ($org_id) {
            self::output_org(intval($org_id));
        } elseif ($artist_id) {
            self::output_artist(intval($artist_id));
        } elseif ($all_feed) {
            self::output_all();
        } elseif ($json_feed) {
            self::output_json_feed();
        } elseif ($rss_feed) {
            self::output_rss_feed();
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
        $events = self::query_events();

        header('Content-Type: text/calendar; charset=utf-8');
        $filename = 'all-events.ics';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo self::build_all_ics($events);
        exit;
    }

    private static function output_json_feed(): void
    {
        $hash = md5($_SERVER['REQUEST_URI']);
        $cached = get_transient('ap_feed_' . $hash);
        if ($cached !== false) {
            header('Content-Type: application/json');
            echo $cached;
            FeedAccessLogger::log('json', $hash, get_current_user_id());
            exit;
        }
        $events = self::query_events();
        $items = [];
        foreach ($events as $event) {
            $items[] = [
                'id'    => $event->ID,
                'title' => $event->post_title,
                'start' => get_post_meta($event->ID, 'event_start_date', true),
                'end'   => get_post_meta($event->ID, 'event_end_date', true),
                'url'   => get_permalink($event),
            ];
        }
        $json = wp_json_encode($items);
        set_transient('ap_feed_' . $hash, $json, 15 * MINUTE_IN_SECONDS);
        header('Content-Type: application/json');
        echo $json;
        FeedAccessLogger::log('json', $hash, get_current_user_id());
        exit;
    }

    private static function output_rss_feed(): void
    {
        $hash = md5($_SERVER['REQUEST_URI']);
        $cached = get_transient('ap_feed_' . $hash);
        if ($cached !== false) {
            header('Content-Type: application/rss+xml; charset=utf-8');
            echo $cached;
            FeedAccessLogger::log('rss', $hash, get_current_user_id());
            exit;
        }
        $events = self::query_events();
        ob_start();
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<rss version=\"2.0\"><channel>";
        echo '<title>ArtPulse Events</title>';
        echo '<link>' . esc_url(home_url()) . '</link>';
        foreach ($events as $event) {
            echo '<item>';
            echo '<title>' . esc_html($event->post_title) . '</title>';
            echo '<link>' . esc_url(get_permalink($event)) . '</link>';
            $date = get_post_meta($event->ID, 'event_start_date', true);
            echo '<pubDate>' . gmdate('r', strtotime($date)) . '</pubDate>';
            echo '<description>' . esc_html(wp_trim_words($event->post_content, 20)) . '</description>';
            echo '</item>';
        }
        echo '</channel></rss>';
        $rss = ob_get_clean();
        set_transient('ap_feed_' . $hash, $rss, 15 * MINUTE_IN_SECONDS);
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo $rss;
        FeedAccessLogger::log('rss', $hash, get_current_user_id());
        exit;
    }

    private static function query_events(): array
    {
        $args = [
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [],
            'tax_query'      => [],
        ];
        if (!empty($_GET['org'])) {
            $args['meta_query'][] = [
                'key'   => '_ap_event_organization',
                'value' => absint($_GET['org']),
            ];
        }
        if (!empty($_GET['region'])) {
            $args['meta_query'][] = [
                'key'   => 'event_state',
                'value' => sanitize_text_field($_GET['region']),
            ];
        }
        if (!empty($_GET['tag'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'post_tag',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($_GET['tag']),
            ];
        }
        if (empty($args['tax_query'])) {
            unset($args['tax_query']);
        }
        if (empty($args['meta_query'])) {
            unset($args['meta_query']);
        }
        return get_posts($args);
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
