<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\ActivityLogger;
use ArtPulse\Core\DashboardWidgetRegistry;

class ActivityFeedWidget {
    public static function can_view(): bool {
        $role = \ArtPulse\Core\DashboardController::get_role( get_current_user_id() );
        return in_array( $role, [ 'member', 'artist', 'organization' ], true );
    }

    public static function register(): void
    {
        DashboardWidgetRegistry::register(
            'activity_feed',
            __('Activity Feed', 'artpulse'),
            'list-view',
            __('Recent user activity.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => ['member', 'artist', 'organization'] ]
        );
    }

    public static function render(): void
    {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;

        if ( ! self::can_view() ) {
            echo '<p class="ap-widget-no-access">' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p>';
            return;
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            esc_html_e('Please log in to view your activity.', 'artpulse');
            return;
        }
        $org_id = intval(get_user_meta($user_id, 'ap_organization_id', true));
        $logs = ActivityLogger::get_logs($org_id ?: null, $user_id, 10);
        if (empty($logs)) {
            esc_html_e('No recent activity.', 'artpulse');
            return;
        }
        echo '<ul class="ap-activity-feed">';
        foreach ($logs as $row) {
            echo '<li>' . esc_html($row->description) . ' <em>' .
                esc_html(date_i18n(get_option('date_format') . ' H:i', strtotime($row->logged_at))) .
                '</em></li>';
        }
        echo '</ul>';
    }
}

ActivityFeedWidget::register();
