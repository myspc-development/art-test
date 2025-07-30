<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Dashboard\WidgetGuard;

/**
 * Initialize dashboard widget placeholders when enabled.
 */
function ap_placeholder_bootstrap(): void
{
    $enabled = defined('AP_ENABLE_WIDGET_PLACEHOLDERS')
        ? AP_ENABLE_WIDGET_PLACEHOLDERS
        : (bool) get_option('ap_enable_widget_placeholders', '1');

    $enabled = apply_filters('ap_widget_placeholder_enabled', $enabled);
    if (!$enabled) {
        return;
    }

    $load_guard = static function () {
        WidgetGuard::init();
    };

    if (did_action('artpulse_widgets_registered')) {
        $load_guard();
    } else {
        add_action('artpulse_widgets_registered', $load_guard, 10);
        // Fallback for older code that doesn't trigger the hook.
        add_action('init', $load_guard, 20);
    }
}
add_action('plugins_loaded', 'ap_placeholder_bootstrap');
