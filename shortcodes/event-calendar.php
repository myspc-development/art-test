<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output container for the event calendar.
 */
function artpulse_event_calendar_shortcode(array $atts = []): string
{
    $atts = shortcode_atts([
        'initial_view' => 'month',
        'geo'          => 'auto',
    ], $atts, 'ap_event_calendar');

    // Enqueue calendar assets only when the shortcode is used.
    $base = plugin_dir_url(dirname(__FILE__));
    wp_enqueue_style('ap-event-calendar', $base . 'assets/css/calendar.css', [], defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false);
    wp_enqueue_script('ap-event-calendar', $base . 'assets/js/calendar.js', [], defined('ARTPULSE_VERSION') ? ARTPULSE_VERSION : false, true);
    if (function_exists('wp_script_add_data')) {
        wp_script_add_data('ap-event-calendar', 'type', 'module');
    }

    $data = sprintf(' data-initial-view="%s" data-geo="%s"', esc_attr($atts['initial_view']), esc_attr($atts['geo']));
    return '<div id="ap-event-calendar" class="ap-event-calendar"' . $data . '></div>';
}
add_shortcode('ap_event_calendar', 'artpulse_event_calendar_shortcode');
