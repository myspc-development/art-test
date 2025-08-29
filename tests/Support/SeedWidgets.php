<?php
declare(strict_types=1);

use ArtPulse\Core\DashboardWidgetRegistry;

if (!function_exists('ap_widget_alpha_markup')) {
    function ap_widget_alpha_markup(): string { return '<p>alpha</p>'; }
}
if (!function_exists('ap_widget_beta_markup')) {
    function ap_widget_beta_markup(): string { return '<p>beta</p>'; }
}
if (!function_exists('ap_widget_gamma_markup')) {
    function ap_widget_gamma_markup(): string { return '<p>gamma</p>'; }
}

if (!function_exists('ap_seed_dashboard_widgets_bootstrap')) {
    function ap_seed_dashboard_widgets_bootstrap(): void {
        if (!class_exists(DashboardWidgetRegistry::class)) {
            return;
        }
        DashboardWidgetRegistry::register(
            'widget_alpha',
            'Alpha',
            '',
            '',
            'ap_widget_alpha_markup',
            [
                'roles' => ['member'],
                'section' => 'Insights',
            ]
        );
        DashboardWidgetRegistry::register(
            'widget_beta',
            'Beta',
            '',
            '',
            'ap_widget_beta_markup',
            [
                'roles' => ['artist'],
                'section' => 'Actions',
            ]
        );
        DashboardWidgetRegistry::register(
            'widget_gamma',
            'Gamma',
            '',
            '',
            'ap_widget_gamma_markup',
            [
                'roles' => ['organization'],
                'section' => 'Insights',
            ]
        );
    }
}
