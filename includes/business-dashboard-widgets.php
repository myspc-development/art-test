<?php
/**
 * Business dashboard widgets for analytics and sales insights.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_widget_site_stats(int $user_id = 0, array $vars = []): string
{
    return ap_load_dashboard_template('widgets/site-stats.php', $vars);
}

function ap_widget_lead_capture(int $user_id = 0, array $vars = []): string
{
    return ap_load_dashboard_template('widgets/lead-capture.php', $vars);
}

function ap_widget_sales_summary(int $user_id = 0, array $vars = []): string
{
    return ap_load_dashboard_template('widgets/sales-summary.php', $vars);
}

function ap_register_business_dashboard_widgets(): void
{
    DashboardWidgetRegistry::register(
        'site_stats',
        __('Site Stats', 'artpulse'),
        'chart-bar',
        __('Overall site traffic and engagement metrics.', 'artpulse'),
        'ap_widget_site_stats',
        [
            'category'   => 'analytics',
            'roles'      => ['administrator'],
            'visibility' => 'public',
            'capability' => 'manage_options',
        ]
    );

    DashboardWidgetRegistry::register(
        'lead_capture',
        __('Lead Capture', 'artpulse'),
        'megaphone',
        __('Recent leads collected from forms.', 'artpulse'),
        'ap_widget_lead_capture',
        [
            'category'   => 'marketing',
            'visibility' => 'public',
            'roles'      => ['administrator'],
            'capability' => 'manage_options',
        ]
    );

    DashboardWidgetRegistry::register(
        'sales_summary',
        __('Sales Summary (Admin)', 'artpulse'),
        'chart-pie',
        __('Sales totals for the selected period.', 'artpulse'),
        'ap_widget_sales_summary',
        [
            'visibility' => 'public',
            'category'   => 'commerce',
            'roles'      => ['administrator'],
            'capability' => 'manage_options',
        ]
    );
}
add_action('artpulse_register_dashboard_widget', 'ap_register_business_dashboard_widgets');
