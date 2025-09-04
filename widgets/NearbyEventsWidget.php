<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

/**
 * Wrapper widget for nearby events shortcode.
 */
class NearbyEventsWidget {
	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			self::icon(),
			self::description(),
			array( self::class, 'render' ),
			array( 'roles' => self::roles() )
		);

		// Legacy alias used in older configs.
		if ( ! DashboardWidgetRegistry::exists( 'widget_widget_near_me' ) ) {
			DashboardWidgetRegistry::register(
				'widget_widget_near_me',
                                sprintf( esc_html__( '%1$s (Legacy)', 'artpulse' ), self::label() ),
                                self::icon(),
                                self::description(),
				array( self::class, 'render' ),
				array( 'roles' => self::roles() )
			);
		}
	}

	public static function id(): string {
		return 'widget_near_me_events'; }

	public static function label(): string {
		return esc_html__( 'Nearby Events', 'artpulse' ); }

	public static function roles(): array {
		return array( 'member' ); }

	public static function description(): string {
		return esc_html__( 'Events near your location.', 'artpulse' ); }

	public static function icon(): string {
		return 'location'; }

	public static function render( int $user_id = 0 ): string {
		return '<div class="inside">' . do_shortcode( '[near_me_events]' ) . '</div>';
	}

	public static function render_placeholder(): string {
		return '<div data-widget="' . esc_attr( self::id() ) . '" data-widget-id="' . esc_attr( self::id() ) . '" class="dashboard-widget"><div class="inside"><div class="ap-widget-placeholder">' .
			esc_html__( 'Nearby events widget is under construction.', 'artpulse' ) .
			'</div></div></div>';
	}
}

NearbyEventsWidget::register();
