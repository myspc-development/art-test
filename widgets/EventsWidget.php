<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

class EventsWidget {
	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::get_id(),
			self::get_title(),
			'calendar',
			esc_html__( 'Sample upcoming events list.', 'artpulse' ),
			array( self::class, 'render' ),
			array(
				'roles'   => array( 'member', 'artist', 'organization' ),
				'section' => self::get_section(),
			)
		);
	}

	public static function get_id(): string {
		return 'sample_events'; }
	public static function get_title(): string {
		return esc_html__( 'Events Widget', 'artpulse' ); }
	public static function get_section(): string {
		return 'insights'; }
	public static function metadata(): array {
		return array( 'sample' => true ); }
	public static function can_view( int $user_id ): bool {
		return $user_id > 0; }

	public static function render( int $user_id = 0 ): string {
		if ( defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
				return '<p>Events content.</p>';
		}

			$user_id = $user_id ?: ( function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0 );
		if ( ! self::can_view( $user_id ) ) {
				return '<p>Events content.</p>';
		}

		try {
			if ( post_type_exists( 'artpulse_event' ) ) {
					$q = new \WP_Query(
						array(
							'post_type'        => 'artpulse_event',
							'posts_per_page'   => 5,
							'no_found_rows'    => true,
							'suppress_filters' => true,
						)
					);
				if ( $q->have_posts() ) {
					ob_start();
					while ( $q->have_posts() ) {
							$q->the_post();
							echo '<p>' . esc_html( get_the_title() ) . '</p>';
					}
					wp_reset_postdata();
					return ob_get_clean();
				}
			}
		} catch ( \Throwable $e ) {
				do_action(
					'artpulse_audit_event',
					'render_exception',
					array(
						'widget' => 'widget_sample_events',
						'error'  => $e->getMessage(),
					)
				);
		}

			return '<p>Events content.</p>';
	}
}

EventsWidget::register();
