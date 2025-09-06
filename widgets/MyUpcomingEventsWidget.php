<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

class MyUpcomingEventsWidget {
	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::get_id(),
			self::get_title(),
			'calendar',
			esc_html__( 'List of your upcoming events', 'artpulse' ),
			array( self::class, 'render' ),
			array(
				'roles'   => array( 'member', 'artist' ),
				'section' => self::get_section(),
			)
		);
	}

	public static function get_id(): string {
		return 'my_upcoming_events'; }
	public static function get_title(): string {
		return esc_html__( 'My Upcoming Events', 'artpulse' ); }
	public static function get_section(): string {
		return 'insights'; }

	public static function can_view( int $user_id ): bool {
		return $user_id > 0;
	}

	public static function render( int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();
		if ( ! self::can_view( $user_id ) ) {
			return '<div class="notice notice-error"><p>' . esc_html__( 'Please log in.', 'artpulse' ) . '</p></div>';
		}

		$rsvp_ids = get_user_meta( $user_id, 'ap_rsvp_events', true );
		if ( ! is_array( $rsvp_ids ) ) {
			$rsvp_ids = array();
		}

		$authored_ids = get_posts(
			array(
				'post_type'        => 'artpulse_event',
				'post_status'      => 'publish',
				'author'           => $user_id,
				'fields'           => 'ids',
				'nopaging'         => true,
				'suppress_filters' => true,
			)
		);

		$event_ids = array_unique( array_merge( $rsvp_ids, $authored_ids ) );
		if ( empty( $event_ids ) ) {
			return '<div class="ap-widget-empty">' . esc_html__( 'No upcoming events.', 'artpulse' ) . '</div>';
		}

		$today = current_time( 'Y-m-d' );
		$q     = new \WP_Query(
			array(
				'post_type'        => 'artpulse_event',
				'post__in'         => $event_ids,
				'posts_per_page'   => 5,
				'orderby'          => 'meta_value',
				'order'            => 'ASC',
				'meta_key'         => '_ap_event_date',
				'meta_query'       => array(
					array(
						'key'     => '_ap_event_date',
						'value'   => $today,
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
				'suppress_filters' => true,
			)
		);

		if ( ! $q->have_posts() ) {
			return '<div class="ap-widget-empty">' . esc_html__( 'No upcoming events.', 'artpulse' ) . '</div>';
		}

		ob_start();
		echo '<ul class="ap-event-list">';
		while ( $q->have_posts() ) {
			$q->the_post();
			echo '<li>' . esc_html( get_the_title() ) . '</li>';
		}
		echo '</ul>';
		wp_reset_postdata();
		return ob_get_clean();
	}
}

MyUpcomingEventsWidget::register();
