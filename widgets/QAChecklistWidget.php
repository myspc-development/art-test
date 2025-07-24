<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Dashboard widget for QA testers to verify basic builder functionality.
 */
class QAChecklistWidget {
    public static function register(): void {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void {
        wp_add_dashboard_widget('ap_qa_checklist', __('QA Checklist', 'artpulse'), [self::class, 'render']);
    }

    public static function render(): void {
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
        echo '<ol class="ap-qa-checklist">';
        echo '<li>' . esc_html__('Switch roles in builder', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Drag and drop widgets', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Save and reload preview', 'artpulse') . '</li>';
        echo '<li>' . esc_html__('Submit feedback below', 'artpulse') . '</li>';
        echo '</ol>';
        echo '<p><input type="text" placeholder="' . esc_attr__('Your notes...', 'artpulse') . '" style="width:100%" /></p>';
    }
}

QAChecklistWidget::register();
