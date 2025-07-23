<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Panel to provide sharing and embed code for organization widgets.
 */
class OrgWidgetSharingPanel
{
    public static function register(): void
    {
        add_action('add_meta_boxes', [self::class, 'add_box']);
    }

    public static function add_box(): void
    {
        add_meta_box('ap_widget_sharing', __('Widget Sharing', 'artpulse'), [self::class, 'render'], 'dashboard_widget', 'side');
    }

    public static function render(): void
    {
        $id = get_the_ID();
        $code = '<script src="' . esc_url(rest_url('widgets/embed.js')) . '?id=' . $id . '"></script>';
        echo '<p>' . esc_html__('Copy this code to embed on another site:', 'artpulse') . '</p>';
        echo '<textarea readonly style="width:100%" rows="3">' . esc_textarea($code) . '</textarea>';
    }
}

OrgWidgetSharingPanel::register();
