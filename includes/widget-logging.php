<?php
if (!defined('ABSPATH')) { exit; }

if (!apply_filters('ap_enable_widget_logging', false)) {
    return;
}

function ap_log_widget_render(string $widget_id, int $user_id): void {
    error_log(sprintf('Widget %s rendered for user %d', $widget_id, $user_id));
}
add_action('ap_widget_rendered', 'ap_log_widget_render', 10, 2);

function ap_log_widget_hidden(string $widget_id, int $user_id): void {
    error_log(sprintf('Widget %s hidden for user %d', $widget_id, $user_id));
}
add_action('ap_widget_hidden', 'ap_log_widget_hidden', 10, 2);
