<?php

namespace ArtPulse\Admin;

/**
 * Registers a wp-admin dashboard widget that embeds the frontend dashboard
 * output so administrators and members can access it from within wp-admin.
 */
class FrontendDashboardWidget
{
    public static function register(): void
    {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget(): void
    {
        if (!current_user_can('view_artpulse_dashboard')) {
            return;
        }

        wp_add_dashboard_widget(
            'ap_member_dashboard',
            __('My ArtPulse Dashboard', 'artpulse'),
            [self::class, 'render_widget']
        );
    }

    public static function render_widget(): void
    {
        echo do_shortcode('[ap_user_dashboard]');
    }
}

