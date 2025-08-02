<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\ActivityLogger;
use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ActivityFeedWidget implements DashboardWidgetInterface {
    public static function can_view(): bool {
        $role = \ArtPulse\Core\DashboardController::get_role( get_current_user_id() );
        return in_array( $role, self::roles(), true );
    }

    public static function id(): string
    {
        return 'activity_feed';
    }

    public static function label(): string
    {
        return __('Activity Feed', 'artpulse');
    }

    public static function roles(): array
    {
        return [ 'member', 'artist', 'organization' ];
    }

    public static function register(): void
    {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'list-view',
            __('Recent user activity.', 'artpulse'),
            [self::class, 'render'],
            [ 'roles' => self::roles() ]
        );
    }

    public static function render(): string
    {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return '';

        if ( ! self::can_view() ) {
            $msg = '<p class="ap-widget-no-access">' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p>';
            echo $msg;
            return $msg;
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            $msg = esc_html__('Please log in to view your activity.', 'artpulse');
            echo $msg;
            return $msg;
        }
        $org_id = intval(get_user_meta($user_id, 'ap_organization_id', true));
        $logs = ActivityLogger::get_logs($org_id ?: null, $user_id, 10);
        if (empty($logs)) {
            $msg = esc_html__('No recent activity.', 'artpulse');
            echo $msg;
            return $msg;
        }
        ob_start();
        echo '<section data-widget="' . esc_attr(self::id()) . '" class="ap-widget ap-' . esc_attr(self::id()) . '">';
        echo '<ul class="ap-activity-feed">';
        foreach ($logs as $row) {
            echo '<li>' . esc_html($row->description) . ' <em>' .
                esc_html(date_i18n(get_option('date_format') . ' H:i', strtotime($row->logged_at))) .
                '</em></li>';
        }
        echo '</ul>';
        echo '</section>';
        $output = ob_get_clean();
        echo $output;
        return $output;
    }
}

ActivityFeedWidget::register();
