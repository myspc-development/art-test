<?php
use ArtPulse\Core\DashboardWidgetRegistry;

function ap_widget_shortcode($atts) {
    $atts = shortcode_atts([
        'id' => '',
    ], $atts);

    $widget_id = sanitize_text_field($atts['id']);
    $widget = DashboardWidgetRegistry::get_widget($widget_id);

    if (!$widget) return '';

    $current_user = wp_get_current_user();
    $roles = $widget['roles'] ?? [];
    if (!array_intersect($current_user->roles, $roles)) {
        return '';
    }

    ob_start();
    locate_template("templates/widgets/{$widget['template']}", true, false);
    return ob_get_clean();
}
add_shortcode('ap_widget', 'ap_widget_shortcode');
