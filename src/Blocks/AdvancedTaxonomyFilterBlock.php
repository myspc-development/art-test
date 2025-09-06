<?php
namespace ArtPulse\Blocks;

use ArtPulse\Admin\EnqueueAssets;
use WP_Block_Type_Registry;

class AdvancedTaxonomyFilterBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/advanced-taxonomy-filter' ) ) {
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

				EnqueueAssets::register_script( 'artpulse-advanced-taxonomy-filter-block', 'assets/js/advanced-taxonomy-filter-block.js', array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch' ), false );
	}

	public static function render_callback( $attributes ) {
		// Render fallback content (frontend rendering is handled by JS)
		return '<div class="artpulse-advanced-taxonomy-filter-block" role="status" aria-live="polite">'
			. '<span class="screen-reader-text">Loading filtered posts...</span>'
			. '<span class="ap-spinner" aria-hidden="true"></span>'
			. '</div>';
	}
}
