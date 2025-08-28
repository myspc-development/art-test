<?php
namespace ArtPulse\Blocks;

class DashboardNewsBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
			return;
		}
		register_block_type_from_metadata( __DIR__ . '/../../blocks/dashboard-news' );
	}

	public static function render_callback( $attributes ) {
		if ( function_exists( 'ap_load_dashboard_template' ) ) {
			return ap_load_dashboard_template( 'widgets/widget-news.php' );
		}
		return '';
	}
}
