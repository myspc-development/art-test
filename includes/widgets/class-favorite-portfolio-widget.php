<?php
/**
 * AP_Favorite_Portfolio_Widget displays the current user's
 * favorite portfolio items via a shortcode.
 */
if (!class_exists('AP_Favorite_Portfolio_Widget') && class_exists('WP_Widget')) {
    class AP_Favorite_Portfolio_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct(
                'ap_favorite_portfolio_widget',
                __('AP Favorite Portfolio', 'artpulse'),
                ['description' => __('Display the user\'s favorite portfolio.', 'artpulse')]
            );
        }

        public function widget($args, $instance) {
            echo $args['before_widget'] ?? '';
            echo do_shortcode('[ap_favorite_portfolio]');
            echo $args['after_widget'] ?? '';
        }
    }
}
