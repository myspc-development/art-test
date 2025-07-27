<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stub rendering callbacks for placeholder widgets.
 * Replace these stubs with real widget implementations as needed.
 */

function render_widget_ap_donor_activity() {
    if (class_exists('DonorActivityWidget')) {
        DonorActivityWidget::render();
    }
}

function render_widget_artist_inbox_preview() {
    if (function_exists('ap_widget_artist_inbox_preview')) {
        echo ap_widget_artist_inbox_preview([]);
    }
}

function render_widget_artist_revenue_summary() {
    if (function_exists('ap_widget_artist_revenue_summary')) {
        echo ap_widget_artist_revenue_summary([]);
    }
}

function render_widget_artist_spotlight() {
    if (function_exists('ap_widget_artist_spotlight')) {
        echo ap_widget_artist_spotlight([]);
    }
}

function render_widget_artpulse_analytics_widget() {
    if (class_exists('OrgAnalyticsWidget')) {
        OrgAnalyticsWidget::render();
    }
}



function render_widget_my_favorites() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_my_favorites();
}



function render_widget_nearby_events_map() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_nearby_events_map();
}

function render_widget_news_feed() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_news();
}



function render_widget_qa_checklist() {
    if (class_exists('QAChecklistWidget')) {
        QAChecklistWidget::render();
    }
}

function render_widget_revenue_summary() {
    if (function_exists('ap_widget_artist_revenue_summary')) {
        echo ap_widget_artist_revenue_summary([]);
    }
}









function render_widget_webhooks() {
    if (function_exists('ap_widget_webhooks')) {
        echo ap_widget_webhooks([]);
    }
}



function render_widget_widget_events() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_events();
}

function render_widget_widget_favorites() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_favorites();
}

function render_widget_widget_my_favorites() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_my_favorites();
}

function render_widget_widget_nearby_events_map() {
    \ArtPulse\Core\DashboardWidgetRegistry::render_widget_nearby_events_map();
}













