<?php
namespace ArtPulse\Frontend;

class CollectionsShortcode {

	public static function register(): void {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_collections', 'Collections Grid', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function enqueue(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
	}

	public static function render( $atts ): string {
		$atts = shortcode_atts(
			array(
				'posts_per_page' => 12,
				'author'         => '',
			),
			$atts,
			'ap_collections'
		);

		$args = array(
			'post_type'      => 'ap_collection',
			'post_status'    => 'publish',
			'posts_per_page' => intval( $atts['posts_per_page'] ),
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);

		if ( $atts['author'] !== '' ) {
			$args['author'] = $atts['author'];
		}

		$query = new \WP_Query( $args );

		if ( empty( $query->posts ) ) {
			return '<p>' . esc_html__( 'No collections found.', 'artpulse' ) . '</p>';
		}

		ob_start();
		echo '<div class="ap-collections-grid">';
		foreach ( $query->posts as $cid ) {
			echo ap_get_collection_card( $cid );
		}
		echo '</div>';

		return ob_get_clean();
	}
}
