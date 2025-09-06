<?php
namespace ArtPulse\Blocks;

use ArtPulse\Admin\EnqueueAssets;
use ArtPulse\Frontend\WidgetEmbedShortcode;

class WidgetEmbedBlock {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'artpulse/widget-embed',
			array(
				'editor_script'   => 'artpulse-widget-embed-block',
				'render_callback' => array( self::class, 'render_callback' ),
				'attributes'      => array(
					'widgetId' => array( 'type' => 'integer' ),
				),
			)
		);

				EnqueueAssets::register_script( 'artpulse-widget-embed-block', 'assets/js/widget-embed-block.js', array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-editor' ), false );
	}

	public static function render_callback( array $attributes ): string {
		$id = intval( $attributes['widgetId'] ?? 0 );
		if ( ! $id ) {
			return '';
		}

		return WidgetEmbedShortcode::render( array( 'id' => $id ) );
	}
}
