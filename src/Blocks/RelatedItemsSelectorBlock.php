<?php
namespace ArtPulse\Blocks;

use ArtPulse\Admin\EnqueueAssets;
use WP_Block_Type_Registry;

class RelatedItemsSelectorBlock {

	public static function register() {
		add_action( 'init', array( self::class, 'register_block_and_meta' ) );
	}

	public static function register_block_and_meta() {
		// Register block editor script (adjust the path accordingly)
				EnqueueAssets::register_script(
					'artpulse-related-items-selector',
					'assets/js/blocks/related-items-selector.js',
					array(
						'wp-blocks',
						'wp-element',
						'wp-components',
						'wp-data',
						'wp-editor',
						'wp-api-fetch',
					),
					false
				);

		// Example: Register post meta fields with REST API enabled

		// Meta for multiple related artworks for artists (array)
				register_post_meta(
					'artpulse_artist',
					'_ap_artist_artworks',
					array(
						'show_in_rest'  => array(
							'schema' => array(
								'type'  => 'array',
								'items' => array( 'type' => 'integer' ),
							),
						),
						'single'        => false,
						'type'          => 'array',
						'auth_callback' => function () {
								return current_user_can( 'edit_posts' );
						},
					)
				);

		// Meta for single related artist for artwork (integer)
		register_post_meta(
			'artpulse_artwork',
			'_ap_artwork_artist',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'integer',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'artpulse/related-items-selector' ) ) {
			return;
		}

		// Register the block type
		register_block_type(
			'artpulse/related-items-selector',
			array(
				'editor_script'   => 'artpulse-related-items-selector',
				'attributes'      => array(
					'postType' => array(
						'type'    => 'string',
						'default' => 'artpulse_artist',
					),
					'metaKey'  => array(
						'type'    => 'string',
						'default' => '_ap_artist_artworks',
					),
					'label'    => array(
						'type'    => 'string',
						'default' => 'Select Related Items',
					),
					'multiple' => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
				'render_callback' => array( self::class, 'render_block' ),
			)
		);
	}

	public static function render_block( $attributes ) {
		$post = get_post();
		if ( ! $post ) {
			return '';
		}

		$meta_key = $attributes['metaKey'] ?? '';
		if ( ! $meta_key ) {
			return '';
		}

		$related = get_post_meta( $post->ID, $meta_key, true );
		if ( ! $related ) {
			return '<p>' . __( 'No related items selected.', 'artpulse' ) . '</p>';
		}

		$ids = is_array( $related ) ? $related : array( $related );

		$is_event = isset( $attributes['postType'] ) && $attributes['postType'] === 'artpulse_event';
		if ( ! $is_event ) {
			foreach ( $ids as $rid ) {
				if ( get_post_type( $rid ) === 'artpulse_event' ) {
					$is_event = true;
					break;
				}
			}
		}

		if ( $is_event ) {
			$cards = array();
			foreach ( $ids as $rid ) {
				$rid = intval( $rid );
				if ( $rid ) {
					$cards[] = ap_get_event_card( $rid );
				}
			}
			if ( $cards ) {
				return '<div class="ap-related-events-grid ap-directory-results">' . implode( '', $cards ) . '</div>';
			}
			return '';
		}

		if ( is_array( $related ) ) {
			$items = array();
			foreach ( $related as $id ) {
				$title = get_the_title( $id );
				if ( $title ) {
					$url                     = get_permalink( $id );
									$items[] = sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $title ) );
				}
			}
			return '<ul class="ap-related-items-list">' . implode( '', $items ) . '</ul>';
		} else {
			$title = get_the_title( $related );
			$url   = get_permalink( $related );
			if ( $title ) {
							return sprintf( '<p><a href="%1$s">%2$s</a></p>', esc_url( $url ), esc_html( $title ) );
			}
			return '';
		}
	}
}
