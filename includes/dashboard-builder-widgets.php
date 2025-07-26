<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

function ap_register_dashboard_builder_widget_map(): void {
    $plugin_dir = dirname(__DIR__);

    global $ap_widget_source_map, $ap_widget_status;

    $member = [
        'news_feed'        => 'ArtPulseNewsFeedWidget.php',
        'rsvp_button'      => 'RSVPButton.jsx',
        'nearby_events_map' => 'NearbyEventsMapWidget.jsx',
        'my_favorites'     => 'MyFavoritesWidget.jsx',
        'event_chat'       => 'EventChatWidget.jsx',
        'share_this_event' => 'ShareThisEventWidget.jsx',
        'artist_inbox_preview' => 'ArtistInboxPreviewWidget.jsx',
        'qa_checklist'     => 'QAChecklistWidget.php',
    ];

    $artist = [
        'artist_inbox_preview' => 'ArtistInboxPreviewWidget.jsx',
        'revenue_summary'    => 'ArtistRevenueSummaryWidget.jsx',
        'artist_spotlight'   => 'ArtistSpotlightWidget.jsx',
    ];

    $organization = [
        'audience_crm'           => 'AudienceCRMWidget.jsx',
        'sponsored_event_config' => 'SponsoredEventConfigWidget.jsx',
        'embed_tool'             => 'EmbedToolWidget.jsx',
        'branding_settings_panel' => 'OrgBrandingSettingsPanel.jsx',
        'ap_donor_activity'       => 'DonorActivityWidget.php',
        'artpulse_analytics_widget' => 'OrgAnalyticsWidget.php',
        'org_widget_sharing'     => 'OrgWidgetSharingPanel.php',
        'webhooks'               => 'WebhooksWidget.php',
        'sponsor_display'        => 'SponsorDisplayWidget.php',
    ];

    $ap_widget_source_map = [
        'member' => $member,
        'artist' => $artist,
        'organization' => $organization,
    ];

    $all = array_merge_recursive($ap_widget_source_map);

    $registered_files = [];
    $missing_files = [];
    $unregistered_files = [];
    $valid_ids = [
        'member' => [],
        'artist' => [],
        'organization' => [],
    ];

    foreach ($ap_widget_source_map as $role => $widgets) {
        foreach ($widgets as $id => $file) {
            $callback = static function () use ($id) {
                echo '<div class="ap-widget-placeholder">' . esc_html($id) . '</div>';
            };

            DashboardWidgetRegistry::register($id, [
                'title' => ucwords(str_replace(['_', '-'], ' ', $id)),
                'render_callback' => $callback,
                'roles' => [$role],
                'file'  => $file,
            ]);

            $path_php = $plugin_dir . '/widgets/' . $file;
            $path_js  = $plugin_dir . '/assets/js/widgets/' . $file;
            $registered_files[$file] = true;
            if (!file_exists($path_php) && !file_exists($path_js)) {
                $missing_files[] = $file;
                error_log('Dashboard widget file missing: ' . $file);
            } else {
                $valid_ids[$role][] = $id;
            }
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
            $callback = static function () use ($id) {
                echo '<div class="ap-widget-placeholder">' . esc_html($id) . '</div>';
            };
            DashboardWidgetRegistry::register($id, [
                'title' => ucwords(str_replace(['_', '-'], ' ', $id)),
                'render_callback' => $callback,
                'roles' => [],
                'file'  => $basename,
            ]);
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

    if (!defined('AP_DB_DEFAULT_LAYOUTS')) {
        $layouts = [];
        foreach ($valid_ids as $role_key => $ids) {
            $layouts[$role_key] = array_map(
                fn($id) => ['id' => $id, 'visible' => true],
                $ids
            );
        }
        define('AP_DB_DEFAULT_LAYOUTS', $layouts);
    }
}
add_action('init', 'ap_register_dashboard_builder_widget_map', 20);

if (defined('WIDGET_DEBUG_MODE') && WIDGET_DEBUG_MODE) {
    require_once dirname(__DIR__) . '/widgets/WidgetStatusPanelWidget.php';
    require_once dirname(__DIR__) . '/widgets/WidgetManifestPanelWidget.php';
}
