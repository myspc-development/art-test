<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
    return;
}
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Rendering callbacks for widgets used by the dashboard builder.
 * A map of widget IDs to callbacks is used to generate wrapper functions
 * so new widgets can be added with a single entry in the array.
 */

function ap_render_js_widget(string $id, array $data = []): void {
    $defaults = [
        'api-root' => esc_url_raw(rest_url()),
        'nonce'    => wp_create_nonce('wp_rest'),
    ];
    $data  = array_merge($defaults, $data);
    $attrs = '';
    foreach ($data as $k => $v) {
        $attrs .= ' data-' . $k . '="' . esc_attr($v) . '"';
    }
    echo '<section data-widget="' . esc_attr($id) . '" data-widget-id="' . esc_attr($id) . '" class="dashboard-widget">';
    echo '<div class="inside">';
    echo '<div id="' . esc_attr($id) . '" class="ap-react-widget"' . $attrs . '></div>';
    echo '</div>';
    echo '</section>';
}



foreach ($widget_callback_map as $id => $_) {
    $fn = 'render_widget_' . $id;
    if (!function_exists($fn)) {
        eval(
            'function ' . $fn . '() {' .
            'global $widget_callback_map;' .
            'return call_user_func_array($widget_callback_map[' . var_export($id, true) . '], func_get_args());' .
            '}'
        );
    }
}

/**
 * Optional: Aliases to satisfy older builder configs or registry mappings.
 * The values may reference widget IDs (without the render_widget_ prefix)
 * or direct callables.
 *
 * @var array<string,string|callable>
 */
$widget_alias_map = [
    'render_widget_widget_ap_donor_activity'        => 'ap_donor_activity',
    'render_widget_widget_artpulse_analytics_widget'=> 'artpulse_analytics_widget',
    'render_widget_widget_audience_crm'             => 'audience_crm',
    'render_widget_widget_branding_settings_panel'  => 'branding_settings_panel',
    'render_widget_widget_embed_tool'               => 'embed_tool',
    'render_widget_widget_event_chat'               => 'event_chat',
    'render_widget_widget_events'                   => [DashboardWidgetRegistry::class, 'render_widget_events'],
    'render_widget_widget_favorites'                => [DashboardWidgetRegistry::class, 'render_widget_favorites'],
    'widget_widget_events'                          => [DashboardWidgetRegistry::class, 'render_widget_events'],
    'widget_widget_favorites'                       => [DashboardWidgetRegistry::class, 'render_widget_favorites'],
    'render_widget_widget_my_favorites'             => 'my_favorites',
    'render_widget_widget_nearby_events_map'        => 'nearby_events_map',
    'render_widget_widget_news_feed'                => 'news_feed',
    'render_widget_widget_org_widget_sharing'       => 'org_widget_sharing',
    'render_widget_widget_qa_checklist'             => 'qa_checklist',
    'render_widget_widget_rsvp_button'              => 'rsvp_button',
    'render_widget_widget_sponsor_display'          => 'sponsor_display',
    'render_widget_widget_sponsored_event_config'   => 'sponsored_event_config',
    'render_widget_widget_artist_inbox_preview'     => 'artist_inbox_preview',
    'render_widget_widget_artist_spotlight'         => 'artist_spotlight',
    'render_widget_widget_revenue_summary'          => 'revenue_summary',
    'render_widget_widget_share_this_event'         => 'share_this_event',
    'render_widget_widget_webhooks'                 => 'webhooks',
    'render_widget_widget_org_event_overview'       => 'org_event_overview',
    'render_widget_widget_org_team_roster'          => 'org_team_roster',
    'render_widget_widget_org_approval_center'      => 'org_approval_center',
    'render_widget_widget_org_ticket_insights'      => 'org_ticket_insights',
    'render_widget_widget_org_broadcast_box'        => 'org_broadcast_box',
    'render_widget_widget_artist_artwork_manager'   => 'artist_artwork_manager',
    'render_widget_widget_artist_audience_insights' => 'artist_audience_insights',
    'render_widget_widget_artist_earnings_summary'  => 'artist_earnings_summary',
    'render_widget_widget_artist_feed_publisher'    => 'artist_feed_publisher',
    'render_widget_widget_collab_requests'          => 'collab_requests',
    'render_widget_widget_onboarding_tracker'       => 'onboarding_tracker',
];

$unresolved_aliases = [];

foreach ($widget_alias_map as $alias => $target) {
    $callback = $target;
    if (is_string($target)) {
        $function = 'render_widget_' . $target;
        if (function_exists($function)) {
            $callback = $function;
        }
    }

    if (is_callable($callback)) {
        eval(
            'function ' . $alias . '() {' .
            'return call_user_func_array(' . var_export($callback, true) . ', func_get_args());' .
            '}'
        );
    } else {
        $unresolved_aliases[] = $alias . ' -> ' . (is_scalar($target) ? $target : gettype($target));
    }
}

if ($unresolved_aliases) {
    error_log('Unresolved widget aliases: ' . implode(', ', $unresolved_aliases));
}

