<?php
namespace ArtPulse\Blocks;

use ArtPulse\Admin\EnqueueAssets;

class TaxonomyFilterBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'artpulse/taxonomy-filter',
			array(
				'editor_script'   => 'artpulse-taxonomy-filter-block',
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
					'terms'    => array(
						'type'    => 'array',
						'default' => array(),
					),
				),
			)
		);

               EnqueueAssets::register_script( 'artpulse-taxonomy-filter-block', 'assets/js/taxonomy-filter-block.js', array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-api-fetch' ), false );
       }

	public static function render_callback( $attributes ) {
		if ( empty( $attributes['postType'] ) || empty( $attributes['taxonomy'] ) ) {
			return '<p>' . esc_html__( 'Please select post type and taxonomy.', 'artpulse' ) . '</p>';
		}

		$post_type = sanitize_text_field( $attributes['postType'] );
		$taxonomy  = sanitize_text_field( $attributes['taxonomy'] );
		$terms     = $attributes['terms'] ?? array();

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => 5,
			'post_status'    => 'publish',
		);

		if ( ! empty( $terms ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms,
				),
			);
		}

		$args['fields']        = 'ids';
		$args['no_found_rows'] = true;

		$query = new \WP_Query( $args );

		if ( empty( $query->posts ) ) {
			return '<p>' . __( 'No posts found.', 'artpulse' ) . '</p>';
		}

		ob_start();
		echo '<ul class="artpulse-taxonomy-filter-list">';
		foreach ( $query->posts as $post_id ) {
			printf(
				'<li><a href="%s">%s</a></li>',
				esc_url( get_permalink( $post_id ) ),
				esc_html( get_the_title( $post_id ) )
			);
		}
		echo '</ul>';

		return ob_get_clean();
	}
}
