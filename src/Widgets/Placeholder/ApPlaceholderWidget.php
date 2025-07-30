<?php
namespace ArtPulse\Widgets\Placeholder;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base placeholder widget when a callback is missing.
 */
class ApPlaceholderWidget
{
    /**
     * Render the placeholder markup.
     *
     * @param array $args Optional arguments including 'debug'.
     * @return void
     */
    public static function render(array $args = []): void
    {
        $markup  = '<div class="ap-widget ap-widget--placeholder">';
        $markup .= '<div class="ap-widget__header">';
        $markup .= '<span class="ap-widget__icon" aria-hidden="true">ⓘ</span>';
        $markup .= '<h3 class="ap-widget__title">' . esc_html__('Widget Unavailable', 'artpulse') . '</h3>';
        $markup .= '</div>';
        $markup .= '<div class="ap-widget__body">';
        $markup .= '<p>' . esc_html__('This widget is not available yet.', 'artpulse') . '</p>';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $debug = $args['debug'] ?? '';
            if ($debug) {
                $markup .= '<small class="ap-widget__debug">' . esc_html($debug) . '</small>';
            }
        }
        $markup .= '</div></div>';

        echo apply_filters('ap_widget_placeholder_markup', $markup, $args);
    }
}
