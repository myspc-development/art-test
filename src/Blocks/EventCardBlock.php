<?php
namespace ArtPulse\Blocks;

use ArtPulse\Frontend\EventCardShortcode;
use WP_Block_Type_Registry;

class EventCardBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
		if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
			return;
		}
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/event-card' ) ) {
			return;
		}

		register_block_type_from_metadata(
			__DIR__ . '/../../blocks/event-card',
			array(
				'render_callback' => array( self::class, 'render_callback' ),
			)
		);
	}

	public static function render_callback( $attributes ) {
		$id = intval( $attributes['id'] ?? 0 );
		return EventCardShortcode::render( array( 'id' => $id ) );
	}
}
