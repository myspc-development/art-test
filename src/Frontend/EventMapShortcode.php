<?php
namespace ArtPulse\Frontend;

class EventMapShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_event_map', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }

        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        wp_enqueue_style(
            'leaflet-css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
        );
        wp_enqueue_script(
            'leaflet-js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            null,
            true
        );
        wp_enqueue_script(
            'ap-event-map',
            $plugin_url . 'assets/js/ap-event-map.js',
            ['leaflet-js'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-event-map', 'APEventMap', [
            'rest' => esc_url_raw(rest_url('artpulse/v1/event-map')),
        ]);
    }

    public static function render(): string
    {
        return '<div id="ap-event-map" style="height:400px"></div>';
    }
}
