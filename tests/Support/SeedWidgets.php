<?php
namespace ArtPulse\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

function widget_alpha(): string { return '<p>alpha</p>'; }
function widget_beta(): string { return '<p>beta</p>'; }
function widget_gamma(): string { return '<p>gamma</p>'; }
function widget_shared(): string { return '<p>shared</p>'; }

function ap_seed_dashboard_widgets_bootstrap(): void {
    DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', __NAMESPACE__.'\\widget_alpha', ['roles'=>['member']]);
    DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', __NAMESPACE__.'\\widget_beta', ['roles'=>['artist']]);
    DashboardWidgetRegistry::register('widget_gamma', 'Gamma', '', '', __NAMESPACE__.'\\widget_gamma', ['roles'=>['organization']]);
    DashboardWidgetRegistry::register('widget_shared', 'Shared', '', '', __NAMESPACE__.'\\widget_shared', ['roles'=>['member','artist','organization']]);
}

add_filter('pre_option_ap_widget_group_visibility', fn() => ['insights'=>true, 'actions'=>true]);
add_filter('pre_option_artpulse_role_layout_member', fn() => [['id'=>'widget_alpha'], ['id'=>'widget_shared']]);
add_filter('pre_option_artpulse_role_layout_artist', fn() => [['id'=>'widget_beta'], ['id'=>'widget_shared']]);
add_filter('pre_option_artpulse_role_layout_organization', fn() => [['id'=>'widget_gamma'], ['id'=>'widget_shared']]);

function mute_missing_widget_warning(int $errno, string $errstr): bool {
    return $errno === E_USER_WARNING && strpos($errstr, 'Dashboard widget not registered') !== false;
}
