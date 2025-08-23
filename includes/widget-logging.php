<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!apply_filters('ap_enable_widget_logging', false)) {
    return;
}

require_once __DIR__ . '/class-widget-logger.php';

new \ArtPulse\WidgetLogger();
