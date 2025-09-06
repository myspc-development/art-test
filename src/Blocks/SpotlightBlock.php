<?php
namespace ArtPulse\Blocks;

use ArtPulse\Admin\EnqueueAssets;

class SpotlightBlock {

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
				return;
		}

				$registry = \WP_Block_Type_Registry::get_instance();
		if ( $registry->is_registered( 'artpulse/spotlights' ) ) {
				return;
		}

				register_block_type(
					'artpulse/spotlights',
					array(
						'editor_script'   => 'artpulse-spotlight-block',
						'render_callback' => array( self::class, 'render_callback' ),
						'attributes'      => array(
							'title'     => array( 'type' => 'string' ),
							'image'     => array( 'type' => 'string' ),
							'visibleTo' => array(
								'type'    => 'array',
								'default' => array( 'member', 'artist' ),
							),
						),
					)
				);

				EnqueueAssets::register_script( 'artpulse-spotlight-block', 'assets/js/spotlight-block.js', array( 'wp-blocks', 'wp-element', 'wp-i18n' ), false );
	}

	public static function render_callback( $attributes ): string {
		if ( is_admin() ) {
			return sprintf(
				'<div class="ap-spotlight-preview"><strong>%s</strong><br><em>Visible to: %s</em></div>',
				esc_html( $attributes['title'] ?? '' ),
				isset( $attributes['visibleTo'] ) ? implode( ', ', (array) $attributes['visibleTo'] ) : ''
			);
		}

		$user  = wp_get_current_user();
		$roles = (array) $user->roles;

		if ( ! array_intersect( $roles, (array) ( $attributes['visibleTo'] ?? array() ) ) ) {
			return '';
		}

				return sprintf( '<div class="ap-spotlight">%1$s</div>', esc_html( $attributes['title'] ?? '' ) );
	}
}
