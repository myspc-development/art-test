<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if (!defined('ABSPATH')) { exit; }
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;

/**
 * Dashboard widget for QA testers to verify basic builder functionality.
 */

class QAChecklistWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'qa_checklist',
            'QA Checklist',
            'yes',
            'Steps to verify basic dashboard features.',
            [self::class, 'render'],
            [ 'roles' => ['member'] ]
        );
    }

    public static function render(): string {
        ob_start();
        echo '<section data-widget="qa_checklist" data-widget-id="qa_checklist" class="dashboard-widget">';
        echo '<div class="inside">';
        echo '<ol>';
        echo '<li>' . esc_html__('Switch roles in builder', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Drag and drop widgets', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Save and reload preview', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Submit feedback below', 'artpulse') . '</li>';
        echo '</ol>';
        echo '<p><input type="text" placeholder="' . esc_attr__('Your notes...', 'artpulse') . '" style="width:100%" /></p>';
        echo '</div>';
        echo '</section>';
        return ob_get_clean();
    }
}

QAChecklistWidget::register();
