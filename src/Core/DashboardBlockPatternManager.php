<?php
namespace ArtPulse\Core;

class DashboardBlockPatternManager {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_patterns' ) );
	}

	public static function register_patterns(): void {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		register_block_pattern_category(
			'artpulse-dashboards',
			array(
				'label' => __( 'Dashboards', 'artpulse' ),
			)
		);

		$patterns = get_option( 'ap_dashboard_widget_config', array() );
		if ( ! is_array( $patterns ) ) {
			return;
		}

		foreach ( $patterns as $role => $markup ) {
			$role = sanitize_key( $role );
			if ( ! is_string( $markup ) || $markup === '' ) {
				continue;
			}
			register_block_pattern(
				'artpulse/dashboard-' . $role,
				array(
					'title'      => ucfirst( $role ) . ' Dashboard',
					'categories' => array( 'artpulse-dashboards' ),
					'content'    => $markup,
				)
			);
		}
	}
}
