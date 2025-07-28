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
    echo '<div id="' . esc_attr($id) . '" class="ap-react-widget"' . $attrs . '></div>';
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

function render_widget_artpulse_analytics_widget(): void {
    if (class_exists('OrgAnalyticsWidget')) {
        OrgAnalyticsWidget::render();
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
function render_widget_widget_ap_donor_activity(): void {
    render_widget_ap_donor_activity();
}

function render_widget_widget_artpulse_analytics_widget(): void {
    render_widget_artpulse_analytics_widget();
}

function render_widget_widget_audience_crm(): void {
    render_widget_audience_crm();
}

function render_widget_widget_branding_settings_panel(): void {
    render_widget_branding_settings_panel();
}

function render_widget_widget_embed_tool(): void {
    render_widget_embed_tool();
}

function render_widget_widget_event_chat(): void {
    render_widget_event_chat();
}

function render_widget_widget_events(): void {
    DashboardWidgetRegistry::render_widget_events();
}

function render_widget_widget_favorites(): void {
    DashboardWidgetRegistry::render_widget_favorites();
}

function render_widget_widget_my_favorites(): void {
    DashboardWidgetRegistry::render_widget_my_favorites();
}

function render_widget_widget_nearby_events_map(): void {
    DashboardWidgetRegistry::render_widget_nearby_events_map();
}

function render_widget_widget_news_feed(): void {
    render_widget_news_feed();
}

function render_widget_widget_org_widget_sharing(): void {
    render_widget_org_widget_sharing();
}

function render_widget_widget_qa_checklist(): void {
    render_widget_qa_checklist();
}

function render_widget_widget_rsvp_button(): void {
    render_widget_rsvp_button();
}

function render_widget_widget_sponsor_display(): void {
    render_widget_sponsor_display();
}

function render_widget_widget_sponsored_event_config(): void {
    render_widget_sponsored_event_config();
}

function render_widget_widget_artist_inbox_preview(): void {
    render_widget_artist_inbox_preview();
}

function render_widget_widget_artist_spotlight(): void {
    render_widget_artist_spotlight();
}

function render_widget_widget_revenue_summary(): void {
    render_widget_revenue_summary();
}

function render_widget_widget_share_this_event(): void {
    render_widget_share_this_event();
}

function render_widget_widget_webhooks(): void {
    render_widget_webhooks();
}

function render_widget_widget_org_event_overview(): void {
    render_widget_org_event_overview();
}

function render_widget_widget_org_team_roster(): void {
    render_widget_org_team_roster();
}

function render_widget_widget_org_approval_center(): void {
    render_widget_org_approval_center();
}

function render_widget_widget_org_ticket_insights(): void {
    render_widget_org_ticket_insights();
}

function render_widget_widget_org_broadcast_box(): void {
    render_widget_org_broadcast_box();
}
