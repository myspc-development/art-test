<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\ActivityLogger;

class ActivityFeedWidget {
    public static function register(): void
    {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void
    {
        wp_add_dashboard_widget('activity_feed', __('Activity Feed', 'artpulse'), [self::class, 'render']);
    }

    public static function render(): void
    {
        if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
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
