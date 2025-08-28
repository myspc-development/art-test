<?php
namespace ArtPulse\Blocks;

class RelatedProjectsBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
			return;
		}
		register_block_type_from_metadata(
			__DIR__ . '/../../blocks/related-projects',
			array(
				'render_callback' => array( self::class, 'render_callback' ),
			)
		);
	}

	public static function render_callback( $attributes ) {
		ob_start();
		$template = locate_template( 'templates/salient/portfolio-related-projects.php' );
		if ( ! $template ) {
			$template = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'templates/salient/portfolio-related-projects.php';
		}
		if ( $template && file_exists( $template ) ) {
			include $template;
		}
		return ob_get_clean();
	}
}
