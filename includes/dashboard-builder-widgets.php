<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

function ap_register_dashboard_builder_widget_map(): void {
    $plugin_dir = dirname(__DIR__);

    $member = [
        'news_feed' => 'ArtPulseNewsFeedWidget.php',
        'rsvp_button' => 'RSVPButtonWidget.jsx',
        'nearby_events_map' => 'NearbyEventsMapWidget.jsx',
        'my_favorites' => 'MyFavoritesWidget.jsx',
        'event_chat' => 'EventChatWidget.jsx',
        'share_this_event' => 'ShareThisEventWidget.jsx',
        'upcoming_events_by_location' => 'UpcomingEventsByLocationWidget.php',
        'followed_artists_activity' => 'FollowedArtistsActivityWidget.php',
        'artist_inbox_preview' => 'ArtistInboxPreviewWidget.php',
        'my_rsvps' => 'MyRSVPsWidget.php',
        'my_shared_events_activity' => 'MySharedEventsActivityWidget.php',
        'recommended_for_you' => 'RecommendedForYouWidget.php',
        'feedback_form' => 'FeedbackFormWidget',
        'help_center' => 'HelpCenterWidget',
        'qa_checklist' => 'QAChecklistWidget.php',
    ];
    $artist = [
        'artist_event_editor' => 'ArtistEventEditor',
        'inbox_preview' => 'InboxPreviewWidget',
        'revenue_summary' => 'RevenueSummaryWidget',
        'community_mentions' => 'CommunityMentionsWidget',
        'fan_poll' => 'FanPollWidget',
        'drag_drop_gallery' => 'DragDropGalleryWidget',
        'content_scheduler' => 'ContentSchedulerWidget',
        'event_performance' => 'EventPerformanceWidget',
        'feedback_form' => 'FeedbackFormWidget',
    ];
    $organization = [
        'crm_overview' => 'CRMOverviewWidget',
        'sponsor_content_manager' => 'SponsorContentManagerWidget',
        'embed_tools' => 'EmbedToolsWidget',
        'org_brand_customizer' => 'OrgBrandCustomizerWidget',
        'submission_pipeline' => 'SubmissionPipelineWidget',
        'multi_org_activity_feed' => 'MultiOrgActivityFeedWidget',
        'team_member_roles' => 'TeamMemberRolesWidget',
        'pinned_announcements' => 'PinnedAnnouncementsWidget',
    ];

    $all = [
        'member' => $member,
        'artist' => $artist,
        'organization' => $organization,
    ];

    $registered_files = [];
    foreach ($all as $role => $widgets) {
        foreach ($widgets as $id => $file) {
            $callback = static function () use ($id) {
                echo '<div class="ap-widget-placeholder">' . esc_html($id) . '</div>';
            };
            DashboardWidgetRegistry::register($id, [
                'title' => ucwords(str_replace(['_', '-'], ' ', $id)),
                'render_callback' => $callback,
                'roles' => [$role],
            ]);

            $path_php = $plugin_dir . '/widgets/' . $file;
            $path_js  = $plugin_dir . '/assets/js/widgets/' . $file;
            $registered_files[$file] = true;
            if (!file_exists($path_php) && !file_exists($path_js)) {
                error_log('Dashboard widget file missing: ' . $file);
            }
        }
    }

    $scanned = array_merge(
        glob($plugin_dir . '/widgets/*.php') ?: [],
        glob($plugin_dir . '/assets/js/widgets/*.jsx') ?: []
    );

    $scanned = array_filter($scanned, static function ($path) {
        $base = basename($path);
        if ($base === 'index.js') {
            return false;
        }
        if (preg_match('/\.(test|spec)\./i', $base)) {
            return false;
        }

        return true;
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
            ]);
            error_log('Dashboard widget file not registered: ' . $basename);
        }
    }

    if (defined('WIDGET_DEBUG_MODE') && WIDGET_DEBUG_MODE) {
        add_action('admin_notices', static function () use ($registered_files, $scanned) {
            $registered = array_keys($registered_files);
            $scanned_basenames = array_map('basename', $scanned);
            $missing = array_diff($registered, $scanned_basenames);
            $extra   = array_diff($scanned_basenames, $registered);
            echo '<div class="notice notice-info"><p>Dashboard Builder Debug:<br>';
            if ($missing) {
                echo 'Missing files: ' . esc_html(implode(', ', $missing)) . '<br>';
            }
            if ($extra) {
                echo 'Unregistered files: ' . esc_html(implode(', ', $extra)) . '<br>';
            }
            echo '</p></div>';
        });
    }

    if (!defined('AP_DB_DEFAULT_LAYOUTS')) {
        define('AP_DB_DEFAULT_LAYOUTS', [
            'member' => array_map(fn($id) => ['id' => $id, 'visible' => true], array_keys($member)),
            'artist' => array_map(fn($id) => ['id' => $id, 'visible' => true], array_keys($artist)),
            'organization' => array_map(fn($id) => ['id' => $id, 'visible' => true], array_keys($organization)),
        ]);
    }
}
add_action('init', 'ap_register_dashboard_builder_widget_map', 20);
