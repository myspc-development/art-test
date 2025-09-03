<?php
namespace ArtPulse\Blocks;

use WP_Block_Type_Registry;

class RelatedProjectsBlock {
        public static function register(): void {
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/related-projects' ) ) {
			return;
		}
		add_action( 'init', array( self::class, 'register_block' ) );
	}

        public static function register_block(): void {
                if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
                        return;
                }
                if ( did_action( 'init' ) && ! doing_action( 'init' ) ) {
                        return;
                }
                if ( WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/related-projects' ) ) {
                        return;
                }
                register_block_type_from_metadata(
                        __DIR__ . '/../../blocks/related-projects',
                        array(
                                'render_callback' => array( self::class, 'render_callback' ),
                        )
                );
                remove_action( 'init', [ self::class, 'register_block' ] );
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
