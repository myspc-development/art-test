<?php
namespace ArtPulse\Blocks;

class FavoritesWidgetBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
			return;
		}
		register_block_type_from_metadata( __DIR__ . '/../../blocks/widget-favorites' );
	}

	public static function render_callback( $attributes ) {
		if ( function_exists( 'ap_widget_favorites' ) ) {
			return ap_widget_favorites();
		}
		return '';
	}
}
