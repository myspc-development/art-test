<?php
namespace ArtPulse\Blocks;

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

               $path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/js/widget-embed-block.js';
               $version = \ArtPulse\Blocks\ap_block_version();
               $ver  = file_exists( $path ) ? filemtime( $path ) : $version;
               wp_register_script(
                       'artpulse-widget-embed-block',
                       plugins_url( 'assets/js/widget-embed-block.js', ARTPULSE_PLUGIN_FILE ),
                       array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-editor' ),
                       $ver
               );
       }

	public static function render_callback( array $attributes ): string {
		$id = intval( $attributes['widgetId'] ?? 0 );
		if ( ! $id ) {
			return '';
		}

		return WidgetEmbedShortcode::render( array( 'id' => $id ) );
	}
}
