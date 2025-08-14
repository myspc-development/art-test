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
     * @param int|null $user_id Optional user ID.
     * @param array    $args    Optional arguments including 'debug'.
     * @return void
     */
    public static function render(?int $user_id = null, array $args = []): void
    {
        $markup  = '<div class="ap-widget ap-widget--placeholder">';
        $markup .= '<div class="ap-widget__header">';
        $markup .= '<span class="ap-widget__icon" aria-hidden="true">ⓘ</span>';
        $markup .= '<h3 class="ap-widget__title">' . esc_html__('Widget Unavailable', 'artpulse') . '</h3>';
        $markup .= '</div>';
        $markup .= '<div class="ap-widget__body">';
        $show_notice = defined('AP_STRICT_FLAGS') && AP_STRICT_FLAGS && current_user_can('manage_options');
        if ($show_notice) {
            $msg = apply_filters('ap_widget_unavailable_message', __('This widget is not available yet.', 'artpulse'), $args);
            $markup .= '<p>' . esc_html($msg) . '</p>';
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $debug = $args['debug'] ?? '';
            if (!empty($debug)) {
                $markup .= '<small class="ap-widget__debug">' . wp_json_encode($debug) . '</small>';
            }
        }
        $markup .= '</div></div>';

        echo apply_filters('ap_widget_placeholder_markup', $markup, $args);
    }
}
