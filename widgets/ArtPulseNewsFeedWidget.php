<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
if (!defined('ABSPATH')) { exit; }

/**
 * Dashboard widget showing recent posts from followed artists or organizations.
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
        if (defined("IS_DASHBOARD_BUILDER_PREVIEW")) return;
        $user_id = get_current_user_id();
        if (!$user_id) {
            esc_html_e('Please log in to view your feed.', 'artpulse');
            return;
        }

        $authors = get_user_meta($user_id, 'ap_following_curators', true);
        $authors = is_array($authors) ? array_map('intval', $authors) : [];
        if (empty($authors)) {
            esc_html_e('Follow artists or organizations to see their latest posts.', 'artpulse');
            return;
        }

        $query = new WP_Query([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            'author__in'     => $authors,
        ]);

        if (!$query->have_posts()) {
            esc_html_e('No recent posts from followed artists.', 'artpulse');
            return;
        }

        echo '<ul class="ap-news-feed-list">';
        while ($query->have_posts()) {
            $query->the_post();
            echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
        }
        echo '</ul>';
        wp_reset_postdata();
    }
}

ArtPulseNewsFeedWidget::register();
