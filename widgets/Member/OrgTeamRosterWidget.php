<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
namespace ArtPulse\Widgets\Member;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class OrgTeamRosterWidget implements DashboardWidgetInterface {
	public static function id(): string {
		return 'org_team_roster'; }
	public static function label(): string {
		return esc_html__( 'Team Roster', 'artpulse' ); }
	public static function roles(): array {
		return array( 'organization' ); }
	public static function description(): string {
		return esc_html__( 'List and manage team members.', 'artpulse' ); }

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			'admin-users',
			self::description(),
			array( self::class, 'render' ),
			array(
				'roles' => self::roles(),
				'lazy'  => true,
			)
		);
	}

	public static function render( int $user_id = 0 ): string {
		ob_start();
		\ap_render_js_widget( self::id() );
		return ob_get_clean();
	}
}

OrgTeamRosterWidget::register();
