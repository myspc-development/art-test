<?php
namespace ArtPulse\Blocks;

class FilteredListShortcodeBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'artpulse/filtered-list-shortcode',
			array(
				'editor_script'   => 'artpulse-filtered-list-shortcode-block',
				'render_callback' => array( self::class, 'render_callback' ),
				'attributes'      => array(
					'postType'     => array(
						'type'    => 'string',
						'default' => 'artpulse_artist',
					),
					'taxonomy'     => array(
						'type'    => 'string',
						'default' => 'artist_specialty',
					),
					'terms'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'postsPerPage' => array(
						'type'    => 'number',
						'default' => 5,
					),
				),
			)
		);

               $path = __DIR__ . '/../../assets/js/filtered-list-shortcode-block.js';
               $version = \ArtPulse\Blocks\ap_block_version();
               $ver  = file_exists( $path ) ? filemtime( $path ) : $version;
               wp_register_script(
                       'artpulse-filtered-list-shortcode-block',
                       plugins_url( 'assets/js/filtered-list-shortcode-block.js', ARTPULSE_PLUGIN_FILE ),
                       array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ),
                       $ver
               );
       }

	public static function render_callback( $attributes ) {
		$atts = array(
			'post_type'      => $attributes['postType'] ?? 'artpulse_artist',
			'taxonomy'       => $attributes['taxonomy'] ?? 'artist_specialty',
			'terms'          => $attributes['terms'] ?? '',
			'posts_per_page' => $attributes['postsPerPage'] ?? 5,
		);

		$shortcode = sprintf(
			'[ap_filtered_list post_type="%s" taxonomy="%s" terms="%s" posts_per_page="%d"]',
			esc_attr( $atts['post_type'] ),
			esc_attr( $atts['taxonomy'] ),
			esc_attr( $atts['terms'] ),
			intval( $atts['posts_per_page'] )
		);

		return do_shortcode( $shortcode );
	}
}
