<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

class ProfileOverviewWidget {
	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::get_id(),
			self::get_title(),
			'user',
			esc_html__( 'Quick stats about your profile', 'artpulse' ),
			array( self::class, 'render' ),
			array(
				'roles'   => array( 'artist' ),
				'section' => self::get_section(),
			)
		);
	}

	public static function get_id(): string {
		return 'profile_overview'; }
	public static function get_title(): string {
		return esc_html__( 'Profile Overview', 'artpulse' ); }
	public static function get_section(): string {
		return 'insights'; }

	public static function can_view( int $user_id ): bool {
		return user_can( $user_id, 'artist' );
	}

	public static function render( int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();
		if ( ! self::can_view( $user_id ) ) {
			return '<div class="notice notice-error"><p>' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p></div>';
		}
		return '<p>' . esc_html__( 'Profile statistics coming soon.', 'artpulse' ) . '</p>';
	}
}

ProfileOverviewWidget::register();
