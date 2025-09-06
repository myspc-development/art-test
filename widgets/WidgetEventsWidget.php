<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

/**
 * Wrapper widget for Upcoming Events.
 */

class WidgetEventsWidget {
	public static function register(): void {
		DashboardWidgetRegistry::register(
			'widget_events',
			esc_html__( 'Upcoming Events', 'artpulse' ),
			'calendar',
			esc_html__( 'Upcoming events for your organization.', 'artpulse' ),
			array( self::class, 'render' ),
			array( 'roles' => array( 'member', 'organization' ) )
		);

		// Legacy alias used in older configs.
		if ( ! DashboardWidgetRegistry::exists( 'widget_widget_events' ) ) {
			DashboardWidgetRegistry::register(
				'widget_widget_events',
				esc_html__( 'Upcoming Events (Legacy)', 'artpulse' ),
				'calendar',
				esc_html__( 'Upcoming events for your organization.', 'artpulse' ),
				array( self::class, 'render' ),
				array( 'roles' => array( 'member', 'organization' ) )
			);
		}
	}

	private static function empty_state( string $msg ): string {
		return '<div class="ap-widget-empty">' . esc_html( $msg ) . '</div>';
	}

	public static function render(): string {
		try {
			ob_start();
			echo '<div data-widget-id="widget_events">';
			if ( function_exists( 'ap_widget_events' ) ) {
				echo wp_kses_post( ap_widget_events( array() ) );
			} else {
				echo self::empty_state( __( 'No upcoming events.', 'artpulse' ) );
			}
			echo '</div>';
			return ob_get_clean();
		} catch ( \Throwable $e ) {
			do_action(
				'artpulse_audit_event',
				'render_exception',
				array(
					'widget' => 'widget_events',
					'error'  => $e->getMessage(),
				)
			);
			return self::empty_state( __( 'Temporarily unavailable.', 'artpulse' ) );
		}
	}
}

WidgetEventsWidget::register();
