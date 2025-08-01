<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;
use ArtPulse\Core\DashboardWidgetRegistry as CoreDashboardWidgetRegistry;

function ap_register_dashboard_builder_widget_map(): void {
    $plugin_dir = dirname(__DIR__);

    global $ap_widget_source_map, $ap_widget_status;

    $member = [
        'activity_feed'   => 'ActivityFeedWidget.php',
        'news_feed'        => 'ArtPulseNewsFeedWidget.php',
        'widget_events'    => 'WidgetEventsWidget.php',
        'widget_favorites' => 'FavoritesOverviewWidget.php',
        'rsvp_button'      => 'RSVPButton.jsx',
        'nearby_events_map' => 'NearbyEventsMapWidget.jsx',
        'my_favorites'     => 'MyFavoritesWidget.jsx',
        'event_chat'       => 'EventChatWidget.jsx',
        'share_this_event' => 'ShareThisEventWidget.jsx',
        'artist_inbox_preview' => 'ArtistInboxPreviewWidget.jsx',
        'qa_checklist'     => 'QAChecklistWidget.php',
    ];

    $artist = [
        'activity_feed'      => 'ActivityFeedWidget.php',
        'artist_inbox_preview' => 'ArtistInboxPreviewWidget.jsx',
        'revenue_summary'    => 'ArtistRevenueSummaryWidget.jsx',
        'artist_spotlight'   => 'ArtistSpotlightWidget.jsx',
    ];

    $organization = [
        'activity_feed'         => 'ActivityFeedWidget.php',
        'audience_crm'           => 'AudienceCRMWidget.jsx',
        'sponsored_event_config' => 'SponsoredEventConfigWidget.jsx',
        'embed_tool'             => 'EmbedToolWidget.jsx',
        'widget_events'         => 'WidgetEventsWidget.php',
        'branding_settings_panel' => 'OrgBrandingSettingsPanel.jsx',
        'ap_donor_activity'       => 'DonorActivityWidget.php',
        'artpulse_analytics_widget' => 'OrgAnalyticsWidget.php',
        'org_widget_sharing'     => 'OrgWidgetSharingPanel.php',
        'webhooks'               => 'WebhooksWidget.php',
        'sponsor_display'        => 'SponsorDisplayWidget.php',
        'org_event_overview'     => 'OrgEventOverviewWidget.jsx',
        'org_team_roster'        => 'OrgTeamRosterWidget.jsx',
        'org_approval_center'    => 'OrgApprovalCenterWidget.jsx',
        'org_ticket_insights'    => 'OrgTicketInsightsWidget.jsx',
        'org_broadcast_box'      => 'OrgBroadcastBoxWidget.jsx',
    ];

    $ap_widget_source_map = [
        'member' => $member,
        'artist' => $artist,
        'organization' => $organization,
    ];

    $registered_files = [];
    $missing_files = [];
    $unregistered_files = [];

    // Build a combined mapping of widget IDs to their files and roles.
    $combined = [];
    foreach ($ap_widget_source_map as $role => $widgets) {
        foreach ($widgets as $id => $file) {
            if (!isset($combined[$id])) {
                $combined[$id] = [
                    'file'  => $file,
                    'roles' => [$role],
                ];
            } else {
                $combined[$id]['roles'][] = $role;
            }
        }
    }

    // Register widgets with the union of roles once per widget ID.
    foreach ($combined as $id => $info) {
        $cb = function_exists('render_widget_' . $id) ? 'render_widget_' . $id : '__return_empty_string';
        if (!DashboardWidgetRegistry::get_widget($id)) {
            DashboardWidgetRegistry::register($id, [
                'title' => ucwords(str_replace(['_', '-'], ' ', $id)),
                'render_callback' => $cb,
                'roles' => array_unique($info['roles']),
                'file'  => $info['file'],
            ]);
        }

        $path_php = $plugin_dir . '/widgets/' . $info['file'];
        $path_js  = $plugin_dir . '/assets/js/widgets/' . $info['file'];
        $registered_files[$info['file']] = true;
        if (!file_exists($path_php) && !file_exists($path_js)) {
            $missing_files[] = $info['file'];
            error_log('Dashboard widget file missing: ' . $info['file']);
        }
    }

    $scanned = array_merge(
        glob($plugin_dir . '/widgets/*.php') ?: [],
        glob($plugin_dir . '/assets/js/widgets/*.jsx') ?: []
    );

    $scanned = array_filter($scanned, static function ($path) {
        $base = basename($path);
        return !in_array($base, ['index.js']) && !preg_match('/\.(test|spec)\./i', $base);
    });

    foreach ($scanned as $path) {
        $basename = basename($path);
        if (!isset($registered_files[$basename])) {
            $id = pathinfo($basename, PATHINFO_FILENAME);
            $cb = function_exists('render_widget_' . $id) ? 'render_widget_' . $id : '__return_empty_string';
            if (!DashboardWidgetRegistry::get_widget($id)) {
                DashboardWidgetRegistry::register($id, [
                    'title' => ucwords(str_replace(['_', '-'], ' ', $id)),
                    'render_callback' => $cb,
                    'roles' => [],
                    'file'  => $basename,
                ]);
            }
            $unregistered_files[] = $basename;
            if (defined('WIDGET_DEBUG_MODE') && WIDGET_DEBUG_MODE) {
                error_log('Dashboard widget file not registered: ' . $basename);
            }
        }
    }

    if (defined('WIDGET_DEBUG_MODE') && WIDGET_DEBUG_MODE) {
        error_log('[DashboardBuilder] Missing widget files: ' . implode(', ', $missing_files));
        error_log('[DashboardBuilder] Unregistered widget files: ' . implode(', ', $unregistered_files));
        add_action('admin_notices', static function () use ($registered_files, $scanned, $missing_files, $unregistered_files) {
            $registered = array_keys($registered_files);
            $scanned_basenames = array_map('basename', $scanned);
            $missing = array_diff($registered, $scanned_basenames);
            $extra   = array_diff($scanned_basenames, $registered);

            echo '<div class="notice notice-info"><p><strong>Dashboard Builder Debug:</strong><br>';
            if ($missing) {
                echo 'Missing files: ' . esc_html(implode(', ', $missing)) . '<br>';
            }
            if ($extra) {
                echo 'Unregistered files: ' . esc_html(implode(', ', $extra)) . '<br>';
            }
            echo '</p></div>';
        });
    }

    $ap_widget_status = [
        'registered'   => array_keys($registered_files),
        'missing'      => $missing_files,
        'unregistered' => $unregistered_files,
    ];

}
add_action('init', 'ap_register_dashboard_builder_widget_map', 20);

function ap_register_builder_core_placeholders(): void {
    global $ap_widget_source_map;

    if (!is_array($ap_widget_source_map)) {
        return;
    }

    $defs = [];
    foreach ($ap_widget_source_map as $role => $widgets) {
        foreach ($widgets as $id => $file) {
            if (!isset($defs[$id])) {
                $defs[$id] = [
                    'roles' => [],
                    'label' => ucwords(str_replace(['_', '-'], ' ', $id)),
                ];
            }
            $defs[$id]['roles'][] = $role;
        }
    }

    foreach ($defs as $id => $info) {
        $core_id = 'widget_' . $id;
        if (!CoreDashboardWidgetRegistry::get_widget($core_id)) {
            CoreDashboardWidgetRegistry::register_widget($core_id, [
                'label'    => $info['label'],
                'callback' => 'render_widget_' . $core_id,
                'roles'    => array_unique($info['roles']),
            ]);
        }
    }
}
add_action('init', 'ap_register_builder_core_placeholders', 25);

if (defined('WIDGET_DEBUG_MODE') && WIDGET_DEBUG_MODE) {
    require_once dirname(__DIR__) . '/widgets/WidgetStatusPanelWidget.php';
    require_once dirname(__DIR__) . '/widgets/WidgetManifestPanelWidget.php';
}
