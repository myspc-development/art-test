<?php
namespace ArtPulse\Blocks;

class FavoritePortfolioBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( \WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/favorite-portfolio' ) ) {
			return;
		}

		register_block_type_from_metadata( __DIR__ . '/../../blocks/favorite-portfolio' );
	}
	public static function render_callback( $attributes ) {
		if ( function_exists( '\\ArtPulse\Frontend\ap_render_favorite_portfolio' ) ) {
			return \ArtPulse\Frontend\ap_render_favorite_portfolio( $attributes );
		}
		if ( function_exists( 'ap_render_favorite_portfolio' ) ) {
			return ap_render_favorite_portfolio( $attributes );
		}
		return '';
	}
}
