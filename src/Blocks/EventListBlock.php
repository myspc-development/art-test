<?php
namespace ArtPulse\Blocks;

use ArtPulse\Frontend\EventListShortcode;

class EventListBlock {
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_block' ) );
	}

	public static function register_block(): void {
        if ( ! function_exists( 'register_block_type_from_metadata' ) ) {
                return;
        }

        if ( function_exists( 'wp_is_block_registered' ) ) {
                if ( wp_is_block_registered( 'artpulse/event-list' ) ) {
                        return;
                }
        } else {
                if ( \WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/event-list' ) ) {
                        return;
                }
        }

        register_block_type_from_metadata(
                __DIR__ . '/../../blocks/event-list',
                array(
                        'render_callback' => array( self::class, 'render_callback' ),
                )
        );
	}

	public static function render_callback( $attributes ) {
		return EventListShortcode::render( $attributes );
	}
}
