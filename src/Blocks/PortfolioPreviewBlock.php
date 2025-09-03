<?php
namespace ArtPulse\Blocks;

class PortfolioPreviewBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
			return;
		}
		if ( ! \WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/portfolio-preview' ) ) {
			register_block_type_from_metadata( __DIR__ . '/../../blocks/widgets/portfolio-preview' );
		}
	}

	public static function render_callback( $attributes ) {
		if ( class_exists( 'ArtPulse\\Widgets' ) ) {
			return \ArtPulse\Widgets::render_portfolio_box();
		}
		return '';
	}
}
