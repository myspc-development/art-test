<?php
namespace ArtPulse\Widgets;

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

    public static function description(): string
    {
        return __('Recent user activity.', 'artpulse');
    }

    public static function icon(): string
    {
        return 'list-view';
    }

    public static function register(): void
    {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            self::icon(),
            self::description(),
            [self::class, 'render'],
            [ 'roles' => self::roles() ]
        );
    }

    public static function render(): string
    {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) {
            return '';
        }

        if (!self::can_view()) {
            return '<p class="ap-widget-no-access">' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p>';
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return esc_html__('Please log in to view your activity.', 'artpulse');
        }

        $org_id = intval(get_user_meta($user_id, 'ap_organization_id', true));
        $logs   = ActivityLogger::get_logs($org_id ?: null, $user_id, 10);
        if (empty($logs)) {
            return esc_html__('No recent activity.', 'artpulse');
        }

        ob_start();
        include __DIR__ . '/../templates/widgets/activity_feed.php';
        return ob_get_clean();
    }
}

ActivityFeedWidget::register();
