<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }
/**
 * Dashboard widgets rendering ArtPulse shortcodes.
 */
use ArtPulse\Core\DashboardWidgetRegistry;

class APShortcodeAdminWidgets {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'ap_event_calendar_widget',
            __('Event Calendar', 'artpulse'),
            'calendar-alt',
            '',
            [self::class, 'render_event_calendar'],
            [ 'roles' => ['organization'], 'capability' => 'manage_options' ]
        );
        DashboardWidgetRegistry::register(
            'ap_notifications_widget',
            __('Notifications', 'artpulse'),
            'bell',
            '',
            [self::class, 'render_notifications'],
            [ 'roles' => ['organization'], 'capability' => 'manage_options' ]
        );
        DashboardWidgetRegistry::register(
            'ap_org_dashboard_widget',
            __('Organization Dashboard', 'artpulse'),
            'building',
            '',
            [self::class, 'render_org_dashboard'],
            [ 'roles' => ['organization'], 'capability' => 'manage_options' ]
        );
    }

    public static function render_event_calendar(): void {
        $uid       = get_current_user_id();
        $org_role  = get_user_meta($uid, 'ap_org_role', true);
        if (!current_user_can('manage_options') && $org_role !== 'organization') {
            echo '<p class="ap-widget-no-access">' . esc_html__("You don’t have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        echo do_shortcode('[ap_event_calendar]');
    }

    public static function render_notifications(): void {
        $uid       = get_current_user_id();
        $org_role  = get_user_meta($uid, 'ap_org_role', true);
        if (!current_user_can('manage_options') && $org_role !== 'organization') {
            echo '<p class="ap-widget-no-access">' . esc_html__("You don’t have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        echo do_shortcode('[ap_notifications]');
    }

    public static function render_org_dashboard(): void {
        $uid       = get_current_user_id();
        $org_role  = get_user_meta($uid, 'ap_org_role', true);
        if (!current_user_can('manage_options') && $org_role !== 'organization') {
            echo '<p class="ap-widget-no-access">' . esc_html__("You don’t have access to view this widget.", 'artpulse') . '</p>';
            return;
        }
        echo do_shortcode('[ap_org_dashboard]');
    }
}
APShortcodeAdminWidgets::register();
