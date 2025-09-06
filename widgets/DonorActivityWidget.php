<?php
if (defined('IS_DASHBOARD_BUILDER_PREVIEW')) return;
namespace ArtPulse\Widgets;

use ArtPulse\Crm\DonationModel;
use ArtPulse\Core\DashboardWidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( defined( 'IS_DASHBOARD_BUILDER_PREVIEW' ) ) {
	return;
}

class DonorActivityWidget {

	public static function can_view( int $user_id ): bool {
		$role = \ArtPulse\Core\DashboardController::get_role( $user_id );
		return $role === 'organization';
	}

	public static function register(): void {
		DashboardWidgetRegistry::register(
			'ap_donor_activity',
			esc_html__( 'Donor Activity', 'artpulse' ),
			'chart-line',
			esc_html__( 'Recent donations for your organization.', 'artpulse' ),
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
		$rows = DonationModel::query( (int) $org_id );
		if ( ! $rows ) {
			esc_html_e( 'No donations found.', 'artpulse' );
			return ob_get_clean();
		}
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Donor', 'artpulse' ) . '</th><th>' . esc_html__( 'Amount', 'artpulse' ) . '</th><th>' . esc_html__( 'Date', 'artpulse' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$user = get_user_by( 'ID', $row['user_id'] );
			$name = $user ? $user->display_name : __( 'Anonymous', 'artpulse' );
			echo '<tr><td>' . esc_html( $name ) . '</td><td>$' . number_format_i18n( $row['amount'], 2 ) . '</td><td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $row['donated_at'] ) ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
		return ob_get_clean();
	}
}

DonorActivityWidget::register();
