<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

class OrgQuickAddEventWidget {

	public static function can_view( int $user_id ): bool {
		if ( ! current_user_can( 'read' ) ) {
			return false;
		}
		$role = \ArtPulse\Core\DashboardController::get_role( $user_id );
		if ( $role !== 'organization' ) {
			return false;
		}
		return (bool) apply_filters( 'ap_can_manage_org', true, $user_id );
	}

	public static function register(): void {
		DashboardWidgetRegistry::register(
			'org_event_quick_add',
			esc_html__( 'Quick Add Event', 'artpulse' ),
			'calendar',
			esc_html__( 'Quickly create a new event.', 'artpulse' ),
			array( self::class, 'render' ),
			array( 'roles' => array( 'organization' ) )
		);
	}

	public static function render( int $user_id = 0 ): string {
		$user_id = $user_id ?: get_current_user_id();

		if ( ! self::can_view( $user_id ) ) {
			return '<div class="notice notice-error"><p>' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p></div>';
		}

		$org_id = get_user_meta( $user_id, 'ap_organization_id', true );
		if ( ! $org_id ) {
			return esc_html__( 'No organization assigned.', 'artpulse' );
		}

		ob_start();
		?>
<div class="ap-widget ap-widget--quick-add-event" data-org-id="<?php echo (int) $org_id; ?>">
		<?php wp_nonce_field( 'ap_org_event_nonce', 'ap_org_event_nonce' ); ?>

	<button id="ap-add-event-btn" class="button button-primary">
		<?php esc_html_e( 'Add Event', 'artpulse' ); ?>
	</button>

	<div id="ap-org-modal" class="ap-modal" hidden>
		<div class="ap-modal__dialog">
			<div class="ap-modal__header">
				<h3><?php esc_html_e( 'Create Event', 'artpulse' ); ?></h3>
				<button type="button" id="ap-modal-close" class="ap-modal__close" aria-label="<?php esc_attr_e( 'Close', 'artpulse' ); ?>">&times;</button>
			</div>
			<div class="ap-modal__body">
				<div
					id="ap-org-event-form"
					data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
					data-action="ap_add_org_event"
					<?php if ( function_exists( 'rest_url' ) ) : ?>
						data-rest-endpoint="<?php echo esc_url( rest_url( 'ap/v1/org/events' ) ); ?>"
						data-rest-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
					<?php endif; ?>
				></div>
			</div>
		</div>
	</div>
</div>
		<?php
		return ob_get_clean();
	}
}

OrgQuickAddEventWidget::register();
