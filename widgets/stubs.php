<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Core\DashboardWidgetRegistry;

/**
 * Rendering callbacks for widgets used by the dashboard builder.
 * These wrappers call existing widget implementations when available
 * or output simple containers for JavaScript-powered widgets.
 */

function ap_render_js_widget(string $id, array $data = []): void {
    $defaults = [
        'api-root' => esc_url_raw(rest_url()),
        'nonce'    => wp_create_nonce('wp_rest'),
    ];
    $data = array_merge($defaults, $data);
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

function render_widget_ap_donor_activity(): void {
    if (class_exists('DonorActivityWidget')) {
        DonorActivityWidget::render();
    }
}

function render_widget_artist_inbox_preview(): void {
    if (function_exists('ap_widget_artist_inbox_preview')) {
        echo ap_widget_artist_inbox_preview([]);
    }
}

function render_widget_artist_revenue_summary(): void {
    if (function_exists('ap_widget_artist_revenue_summary')) {
        echo ap_widget_artist_revenue_summary([]);
    }
}

function render_widget_artist_spotlight(): void {
    if (function_exists('ap_widget_artist_spotlight')) {
        echo ap_widget_artist_spotlight([]);
    }
}

function render_widget_artist_artwork_manager(): void {
    if (function_exists('ap_widget_artist_artwork_manager')) {
        echo ap_widget_artist_artwork_manager([]);
    }
}

function render_widget_artist_audience_insights(): void {
    if (function_exists('ap_widget_artist_audience_insights')) {
        echo ap_widget_artist_audience_insights([]);
    }
}

function render_widget_artist_earnings_summary(): void {
    if (function_exists('ap_widget_artist_earnings_summary')) {
        echo ap_widget_artist_earnings_summary([]);
    }
}

function render_widget_artist_feed_publisher(): void {
    if (function_exists('ap_widget_artist_feed_publisher')) {
        echo ap_widget_artist_feed_publisher([]);
    }
}

function render_widget_collab_requests(): void {
    if (function_exists('ap_widget_collab_requests')) {
        echo ap_widget_collab_requests([]);
    }
}

function render_widget_onboarding_tracker(): void {
    if (function_exists('ap_widget_onboarding_tracker')) {
        echo ap_widget_onboarding_tracker([]);
    }
}

function render_widget_artpulse_analytics_widget(): void {
    if (class_exists('OrgAnalyticsWidget')) {
        echo OrgAnalyticsWidget::render(get_current_user_id());
    }
}

function render_widget_audience_crm(): void {
    ap_render_js_widget('audience_crm');
}

function render_widget_branding_settings_panel(): void {
    ap_render_js_widget('branding_settings_panel');
}

function render_widget_embed_tool(): void {
    ap_render_js_widget('embed_tool');
}

function render_widget_org_event_overview(): void {
    ap_render_js_widget('org_event_overview');
}

function render_widget_org_team_roster(): void {
    ap_render_js_widget('org_team_roster');
}

function render_widget_org_approval_center(): void {
    ap_render_js_widget('org_approval_center');
}

function render_widget_org_ticket_insights(): void {
    ap_render_js_widget('org_ticket_insights');
}

function render_widget_org_broadcast_box(): void {
    ap_render_js_widget('org_broadcast_box');
}

function render_widget_event_chat(): void {
    ap_render_js_widget('event_chat', ['event-id' => 0]);
}

function render_widget_my_favorites(): void {
    if (function_exists('ap_widget_my_favorites')) {
        echo ap_widget_my_favorites([]);
    }
}

function render_widget_my_shared_events_activity(): void {
    if (function_exists('ap_widget_my_shared_events_activity')) {
        echo ap_widget_my_shared_events_activity([]);
    }
}

function render_widget_nearby_events_map(): void {
    DashboardWidgetRegistry::render_widget_nearby_events_map();
}

function render_widget_news_feed(): void {
    if (class_exists('ArtPulseNewsFeedWidget')) {
        ArtPulseNewsFeedWidget::render();
    }
}

function render_widget_org_widget_sharing(): void {
    if (class_exists('OrgWidgetSharingPanel')) {
        OrgWidgetSharingPanel::render();
    }
}

function render_widget_qa_checklist(): void {
    if (class_exists('QAChecklistWidget')) {
        QAChecklistWidget::render();
    }
}

function render_widget_revenue_summary(): void {
    if (function_exists('ap_widget_artist_revenue_summary')) {
        echo ap_widget_artist_revenue_summary([]);
    }
}

function render_widget_rsvp_button(): void {
    ap_render_js_widget('rsvp_button', ['event-id' => 0]);
}

function render_widget_share_this_event(): void {
    ap_render_js_widget('share_this_event');
}

function render_widget_sponsor_display(): void {
    ap_render_js_widget('sponsor_display');
}

function render_widget_sponsored_event_config(): void {
    ap_render_js_widget('sponsored_event_config');
}

function render_widget_webhooks(): void {
    if (function_exists('ap_widget_webhooks')) {
        echo ap_widget_webhooks([]);
    }
}

// Optional: Aliases to satisfy older builder configs or registry mappings
$widget_alias_map = [
    'render_widget_widget_ap_donor_activity'        => 'render_widget_ap_donor_activity',
    'render_widget_widget_artpulse_analytics_widget'=> 'render_widget_artpulse_analytics_widget',
    'render_widget_widget_audience_crm'             => 'render_widget_audience_crm',
    'render_widget_widget_branding_settings_panel'  => 'render_widget_branding_settings_panel',
    'render_widget_widget_embed_tool'               => 'render_widget_embed_tool',
    'render_widget_widget_event_chat'               => 'render_widget_event_chat',
    'render_widget_widget_events'                   => 'ArtPulse\\Core\\DashboardWidgetRegistry::render_widget_events',
    'render_widget_widget_favorites'                => 'ArtPulse\\Core\\DashboardWidgetRegistry::render_widget_favorites',
    'widget_widget_events'                          => 'ArtPulse\\Core\\DashboardWidgetRegistry::render_widget_events',
    'widget_widget_favorites'                       => 'ArtPulse\\Core\\DashboardWidgetRegistry::render_widget_favorites',
    'render_widget_widget_my_favorites'             => 'ArtPulse\\Core\\DashboardWidgetRegistry::render_widget_my_favorites',
    'render_widget_widget_nearby_events_map'        => 'ArtPulse\\Core\\DashboardWidgetRegistry::render_widget_nearby_events_map',
    'render_widget_widget_news_feed'                => 'render_widget_news_feed',
    'render_widget_widget_org_widget_sharing'       => 'render_widget_org_widget_sharing',
    'render_widget_widget_qa_checklist'             => 'render_widget_qa_checklist',
    'render_widget_widget_rsvp_button'              => 'render_widget_rsvp_button',
    'render_widget_widget_sponsor_display'          => 'render_widget_sponsor_display',
    'render_widget_widget_sponsored_event_config'   => 'render_widget_sponsored_event_config',
    'render_widget_widget_artist_inbox_preview'     => 'render_widget_artist_inbox_preview',
    'render_widget_widget_artist_spotlight'         => 'render_widget_artist_spotlight',
    'render_widget_widget_revenue_summary'          => 'render_widget_revenue_summary',
    'render_widget_widget_share_this_event'         => 'render_widget_share_this_event',
    'render_widget_widget_webhooks'                 => 'render_widget_webhooks',
    'render_widget_widget_org_event_overview'       => 'render_widget_org_event_overview',
    'render_widget_widget_org_team_roster'          => 'render_widget_org_team_roster',
    'render_widget_widget_org_approval_center'      => 'render_widget_org_approval_center',
    'render_widget_widget_org_ticket_insights'      => 'render_widget_org_ticket_insights',
    'render_widget_widget_org_broadcast_box'        => 'render_widget_org_broadcast_box',
    'render_widget_widget_artist_artwork_manager'   => 'render_widget_artist_artwork_manager',
    'render_widget_widget_artist_audience_insights' => 'render_widget_artist_audience_insights',
    'render_widget_widget_artist_earnings_summary'  => 'render_widget_artist_earnings_summary',
    'render_widget_widget_artist_feed_publisher'    => 'render_widget_artist_feed_publisher',
    'render_widget_widget_collab_requests'          => 'render_widget_collab_requests',
    'render_widget_widget_onboarding_tracker'       => 'render_widget_onboarding_tracker',
];

foreach ($widget_alias_map as $alias => $target) {
    if (is_callable($target)) {
        eval(sprintf(
            'function %s() { return call_user_func_array(%s, func_get_args()); }',
            $alias,
            var_export($target, true)
        ));
    } else {
        error_log(sprintf('Unresolved widget alias: %s -> %s', $alias, $target));
    }
}
