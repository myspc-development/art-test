<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Simple dashboard widget showing placeholder news content.
 *
 * TODO: Replace with real feed logic per ArtPulse_Member_Dashboard_Roadmap.md.
 */
class ArtPulseNewsFeedWidget {
    public static function register() {
        add_action('wp_dashboard_setup', [self::class, 'add_widget']);
    }

    public static function add_widget() {
        wp_add_dashboard_widget(
            'ap_news_feed_widget',
            __('ArtPulse News Feed', 'artpulse'),
            [self::class, 'render']
        );
    }

    public static function render() {
        echo '<div class="wrap"><p>';
        esc_html_e('Latest news from followed artists will appear here.', 'artpulse');
        echo '</p></div>';
    }
}

ArtPulseNewsFeedWidget::register();
