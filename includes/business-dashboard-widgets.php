<?php
/**
 * Business dashboard widgets for analytics and sales insights.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

function ap_widget_site_stats( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/site-stats.php', $vars );
}

function ap_widget_lead_capture( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/lead-capture.php', $vars );
}

function ap_widget_sales_summary( int $user_id = 0, array $vars = array() ): string {
	return ap_load_dashboard_template( 'widgets/sales-summary.php', $vars );
}

function ap_register_business_dashboard_widgets(): void {
	DashboardWidgetRegistry::register(
		'site_stats',
		'Site Stats',
		'chart-bar',
		'Overall site traffic and engagement metrics.',
		'ap_widget_site_stats',
		array(
			'category'   => 'analytics',
			'roles'      => array( 'administrator' ),
			'visibility' => 'public',
			'capability' => 'manage_options',
		)
	);

	DashboardWidgetRegistry::register(
		'lead_capture',
		'Lead Capture',
		'megaphone',
		'Recent leads collected from forms.',
		'ap_widget_lead_capture',
		array(
			'category'   => 'marketing',
			'visibility' => 'public',
			'roles'      => array( 'administrator' ),
			'capability' => 'manage_options',
		)
	);

	DashboardWidgetRegistry::register(
		'sales_summary',
		'Sales Summary (Admin)',
		'chart-pie',
		'Sales totals for the selected period.',
		'ap_widget_sales_summary',
		array(
			'visibility' => 'public',
			'category'   => 'commerce',
			'roles'      => array( 'administrator' ),
			'capability' => 'manage_options',
		)
	);
}
add_action( 'artpulse_register_dashboard_widget', 'ap_register_business_dashboard_widgets' );
