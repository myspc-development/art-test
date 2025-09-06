<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

/**
 * Dashboard widget showing recent posts from followed artists or organizations.
 */

class ArtPulseNewsFeedWidget {
	public static function register() {
		DashboardWidgetRegistry::register(
			'news_feed',
			esc_html__( 'ArtPulse News Feed', 'artpulse' ),
			'rss',
			esc_html__( 'Latest posts from followed artists.', 'artpulse' ),
			array( self::class, 'render' ),
			array( 'roles' => array( 'member' ) )
		);
	}

	public static function render( int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();
		if ( ! $user_id ) {
			return '<div class="ap-news-feed-widget" data-widget-id="news_feed">' . esc_html__( 'Please log in to view your feed.', 'artpulse' ) . '</div>';
		}

		$authors = get_user_meta( $user_id, 'ap_following_curators', true );
		$authors = is_array( $authors ) ? array_map( 'intval', $authors ) : array();
		if ( empty( $authors ) ) {
			return '<div class="ap-news-feed-widget" data-widget-id="news_feed">' . esc_html__( 'Follow artists or organizations to see their latest posts.', 'artpulse' ) . '</div>';
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'author__in'     => $authors,
			)
		);

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return '<div class="ap-news-feed-widget" data-widget-id="news_feed">' . esc_html__( 'No recent posts from followed artists.', 'artpulse' ) . '</div>';
		}

		ob_start();
		echo '<div class="ap-news-feed-widget" data-widget-id="news_feed">';
		echo '<ul class="ap-news-feed-list">';
		while ( $query->have_posts() ) {
			$query->the_post();
			echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
		}
		echo '</ul>';
		echo '</div>';
		wp_reset_postdata();
		return ob_get_clean();
	}
}

ArtPulseNewsFeedWidget::register();
