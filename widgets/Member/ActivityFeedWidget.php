<?php
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}
namespace ArtPulse\Widgets\Member;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

use ArtPulse\Core\ActivityLogger;
use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class ActivityFeedWidget implements DashboardWidgetInterface {
	public static function can_view( int $user_id = 0 ): bool {
			$user_id = $user_id ?: get_current_user_id();
			$role    = \ArtPulse\Core\DashboardController::get_role( $user_id );
			return in_array( $role, self::roles(), true );
	}

	public static function id(): string {
		return 'activity_feed';
	}

	public static function label(): string {
		return esc_html__( 'Activity Feed', 'artpulse' );
	}

	public static function roles(): array {
		return array( 'member', 'artist', 'organization' );
	}

	public static function description(): string {
		return esc_html__( 'Recent user activity.', 'artpulse' );
	}

	public static function icon(): string {
		return 'list-view';
	}

	public static function register(): void {
		DashboardWidgetRegistry::register(
			self::id(),
			self::label(),
			self::icon(),
			self::description(),
			array( self::class, 'render' ),
			array( 'roles' => self::roles() )
		);
	}

	public static function render( int $user_id = 0 ): string {
				$user_id = $user_id ?: get_current_user_id();

		if ( ! $user_id || ! self::can_view( $user_id ) ) {
				return '<div class="notice notice-error"><p>' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p></div>';
		}

		$org_id = intval( get_user_meta( $user_id, 'ap_organization_id', true ) );
		$logs   = ActivityLogger::get_logs( $org_id ?: null, $user_id, 10 );

		$heading_id = sanitize_title( self::id() ) . '-heading-' . uniqid();

		ob_start();
		?>
		<section role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
			data-widget="<?php echo esc_attr( self::id() ); ?>"
			data-widget-id="<?php echo esc_attr( self::id() ); ?>"
			class="ap-widget ap-<?php echo esc_attr( self::id() ); ?>">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php esc_html_e( 'Recent Activity', 'artpulse' ); ?></h2>
			<?php if ( ! empty( $logs ) ) : ?>
				<ul class="ap-activity-feed">
					<?php foreach ( $logs as $row ) : ?>
						<li><?php echo esc_html( $row->description ); ?> <em><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', strtotime( $row->logged_at ) ) ); ?></em></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php echo esc_html__( 'No recent activity.', 'artpulse' ); ?></p>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}
}

ActivityFeedWidget::register();
