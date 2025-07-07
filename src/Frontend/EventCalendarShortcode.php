<?php
namespace ArtPulse\Frontend;

class EventCalendarShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_event_calendar', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        $local_css  = $plugin_url . 'assets/libs/fullcalendar/main.min.css';
        $local_js   = $plugin_url . 'assets/libs/fullcalendar/main.min.js';

        wp_enqueue_style(
            'fullcalendar-css',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css'
        );
        add_filter(
            'style_loader_tag',
            static function ($html, $handle, $href, $media) use ($local_css) {
                if ($handle === 'fullcalendar-css') {
                    $html = str_replace(
                        '/>',
                        " onerror=\"this.onerror=null;this.href='{$local_css}'\" />",
                        $html
                    );
                }
                return $html;
            },
            10,
            4
        );

        wp_enqueue_script(
            'fullcalendar-js',
            'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.js',
            [],
            null,
            true
        );
        add_filter(
            'script_loader_tag',
            static function ($tag, $handle, $src) use ($local_js) {
                if ($handle === 'fullcalendar-js') {
                    $tag = str_replace(
                        'src="' . esc_url($src) . '"',
                        "onerror=\"this.onerror=null;this.src='{$local_js}'\" src=\"{$src}\"",
                        $tag
                    );
                }
                return $tag;
            },
            10,
            3
        );
        wp_enqueue_script(
            'ap-event-calendar',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-event-calendar.js',
            ['fullcalendar-js', 'jquery'],
            '1.0',
            true
        );

        wp_localize_script('ap-event-calendar', 'APCalendar', [
            'events'    => ap_get_events_for_calendar(),
            'rest_root' => esc_url_raw(rest_url()),
            'close_text' => __('Close', 'artpulse'),
        ]);
    }

    public static function render(): string
    {
        return '<div id="ap-event-calendar"></div>';
    }
}
