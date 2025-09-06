<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets\Member;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ArtistInboxPreviewWidget implements DashboardWidgetInterface {
	public static function id(): string {
		return 'artist_inbox_preview'; }
	public static function label(): string {
		return esc_html__( 'Artist Inbox Preview', 'artpulse' ); }
	public static function roles(): array {
		return array( 'member', 'artist' ); }
	public static function description(): string {
		return esc_html__( 'Recent unread messages from artists.', 'artpulse' ); }

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'inbox',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles'      => self::roles(),
				'category'   => 'engagement',
				'capability' => 'can_receive_messages',
			)
		);
	}

	public static function render( int $user_id = 0 ): string {
		ob_start();
		\ap_render_js_widget( self::id() );
		return ob_get_clean();
	}
}

ArtistInboxPreviewWidget::register();
