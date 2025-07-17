<?php
/**
 * Business dashboard widgets for analytics and sales insights.
 */

if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_widget_site_stats(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/site-stats.php', $vars);
}

function ap_widget_lead_capture(array $vars = []): string
{
    return ap_load_dashboard_template('widgets/lead-capture.php', $vars);
}

function ap_widget_sales_summary(array $vars = []): string
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
            'category' => 'analytics',
            'roles'    => ['administrator'],
        ]
    );

    DashboardWidgetRegistry::register(
        'lead_capture',
        __('Lead Capture', 'artpulse'),
        'megaphone',
        __('Recent leads collected from forms.', 'artpulse'),
        'ap_widget_lead_capture',
        [
            'category' => 'marketing',
            'roles'    => ['administrator'],
        ]
    );

    DashboardWidgetRegistry::register(
        'sales_summary',
        __('Sales Summary (Admin)', 'artpulse'),
        'chart-pie',
        __('Sales totals for the selected period.', 'artpulse'),
        'ap_widget_sales_summary',
        [
            'category' => 'commerce',
            'roles'    => ['administrator'],
        ]
    );
}
add_action('artpulse_register_dashboard_widget', 'ap_register_business_dashboard_widgets');
