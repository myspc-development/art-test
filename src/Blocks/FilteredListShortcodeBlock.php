<?php
namespace ArtPulse\Blocks;

use ArtPulse\Admin\EnqueueAssets;
use WP_Block_Type_Registry;

class FilteredListShortcodeBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

       public static function register_block() {
               if ( ! function_exists( 'register_block_type' ) ) {
                       return;
               }

               $block_name = 'artpulse/filtered-list-shortcode';

               if ( WP_Block_Type_Registry::get_instance()->is_registered( $block_name ) ) {
                       return;
               }

               register_block_type(
                       $block_name,
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

               EnqueueAssets::register_script( 'artpulse-filtered-list-shortcode-block', 'assets/js/filtered-list-shortcode-block.js', array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ), false );
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
