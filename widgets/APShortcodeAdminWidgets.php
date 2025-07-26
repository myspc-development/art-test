<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }
/**
 * Dashboard widgets rendering ArtPulse shortcodes.
 */
class APShortcodeAdminWidgets {
    public static function register(): void {
        add_action('wp_dashboard_setup', [self::class, 'add_widgets']);
    }

    public static function add_widgets(): void {
        $uid = get_current_user_id();
        $org_role = get_user_meta($uid, 'ap_org_role', true);
        if (!current_user_can('manage_options') && $org_role !== 'organization') {
            return;
        }
        wp_add_dashboard_widget('ap_event_calendar_widget', __('Event Calendar', 'artpulse'), [self::class, 'render_event_calendar']);
        wp_add_dashboard_widget('ap_notifications_widget', __('Notifications', 'artpulse'), [self::class, 'render_notifications']);
        wp_add_dashboard_widget('ap_org_dashboard_widget', __('Organization Dashboard', 'artpulse'), [self::class, 'render_org_dashboard']);
    }

    public static function render_event_calendar(): void {
        echo do_shortcode('[ap_event_calendar]');
    }

    public static function render_notifications(): void {
        echo do_shortcode('[ap_notifications]');
    }

    public static function render_org_dashboard(): void {
        echo do_shortcode('[ap_org_dashboard]');
    }
}
APShortcodeAdminWidgets::register();
