<?php
namespace ArtPulse\Frontend;

use ArtPulse\Core\DashboardRenderer;
use ArtPulse\Core\DashboardWidgetRegistry;

class DashboardCard
{
    public static function render(string $widget_id, ?int $user_id = null): string
    {
        $user_id = $user_id ?? get_current_user_id();
        $config  = DashboardWidgetRegistry::get_widget($widget_id, $user_id);
        if (!$config) {
            return '';
        }

        $content = DashboardRenderer::render($widget_id, $user_id);
        if ($content === '') {
            return '';
        }

        $label      = $config['label'] ?? $config['title'] ?? $widget_id;
        $heading_id = $widget_id . '-title';

        return sprintf(
            '<section id="%1$s" class="ap-card" role="region" aria-labelledby="%2$s"><h2 id="%2$s" class="ap-card__title">%3$s</h2><div class="ap-card__body">%4$s</div></section>',
            esc_attr($widget_id),
            esc_attr($heading_id),
            esc_html($label),
            $content
        );
    }
}
