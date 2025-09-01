<?php
namespace ArtPulse\Blocks;

class AdvancedTaxonomyFilterBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'artpulse/advanced-taxonomy-filter',
			array(
				'editor_script'   => 'artpulse-advanced-taxonomy-filter-block',
				'render_callback' => array( self::class, 'render_callback' ),
				'attributes'      => array(
					'postType' => array(
						'type'    => 'string',
						'default' => 'artpulse_artist',
					),
					'taxonomy' => array(
						'type'    => 'string',
						'default' => 'artist_specialty',
					),
				),
			)
		);

               $path = __DIR__ . '/../../assets/js/advanced-taxonomy-filter-block.js';
               $version = \ArtPulse\Blocks\ap_block_version();
               $ver  = file_exists( $path ) ? filemtime( $path ) : $version;
               wp_register_script(
                       'artpulse-advanced-taxonomy-filter-block',
                       plugins_url( 'assets/js/advanced-taxonomy-filter-block.js', ARTPULSE_PLUGIN_FILE ),
                       array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch' ),
                       $ver
               );
       }

	public static function render_callback( $attributes ) {
		// Render fallback content (frontend rendering is handled by JS)
		return '<div class="artpulse-advanced-taxonomy-filter-block" role="status" aria-live="polite">'
			. '<span class="screen-reader-text">Loading filtered posts...</span>'
			. '<span class="ap-spinner" aria-hidden="true"></span>'
			. '</div>';
	}
}
