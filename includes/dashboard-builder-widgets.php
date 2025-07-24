<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

function ap_register_dashboard_builder_widget_map(): void {
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

            $path_php = dirname(__DIR__) . '/widgets/' . $file;
            $path_js  = dirname(__DIR__) . '/assets/js/widgets/' . $file;
            if (!file_exists($path_php) && !file_exists($path_js)) {
                trigger_error('Dashboard widget file missing: ' . $file, E_USER_WARNING);
            }
        }
    }

    $all_files = array_merge(
        glob(dirname(__DIR__) . '/widgets/*.php') ?: [],
        glob(dirname(__DIR__) . '/assets/js/widgets/*.{js,jsx}', GLOB_BRACE) ?: []
    );
    foreach ($all_files as $file_path) {
        $basename = basename($file_path);
        $found = false;
        foreach ($all as $widgets) {
            if (in_array($basename, $widgets, true)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            trigger_error('Dashboard widget file present but not registered: ' . $basename, E_USER_WARNING);
        }
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
