<?php
namespace ArtPulse\Admin;

if (!defined('ABSPATH')) { exit; }

class EngagementAnalyticsDashboard
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Engagement Analytics', 'artpulse'),
            __('Analytics', 'artpulse'),
            'manage_options',
            'artpulse-engagement',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        $script = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-engagement-dashboard.js';
        wp_enqueue_script(
            'ap-engagement-dashboard',
            plugins_url('assets/js/ap-engagement-dashboard.js', ARTPULSE_PLUGIN_FILE),
            ['chart-js'],
            file_exists($script) ? filemtime($script) : '1.0',
            true
        );
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Engagement Analytics', 'artpulse') . '</h1>';
        echo '<canvas id="apEngagementChart" height="160"></canvas>';
        echo '</div>';
    }
}
