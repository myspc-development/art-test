<?php
namespace ArtPulse\Admin;

class MetaBoxesArtwork {

	public static function register() {
		add_action( 'add_meta_boxes', array( self::class, 'add_artwork_meta_boxes' ) );
		add_action( 'save_post_artpulse_artwork', array( self::class, 'save_artwork_meta' ), 10, 2 ); // Corrected CPT slug
		add_action( 'rest_api_init', array( self::class, 'register_rest_fields' ) );
		add_action( 'restrict_manage_posts', array( self::class, 'add_admin_filters' ) );
		add_filter( 'pre_get_posts', array( self::class, 'filter_artworks_admin_query' ) );
	}

	public static function add_artwork_meta_boxes() {
		add_meta_box(
			'ead_artwork_details',
			__( 'Artwork Details', 'artpulse' ),
			array( self::class, 'render_artwork_details' ),
			'artpulse_artwork', // Corrected CPT slug
			'normal',
			'high'
		);
	}

	public static function render_artwork_details( $post ) {
		wp_nonce_field( 'ead_artwork_meta_nonce', 'ead_artwork_meta_nonce_field' );

		$fields = self::get_registered_artwork_meta_fields();

		echo '<table class="form-table">';
		foreach ( $fields as $key => $args ) {
			list($type, $label) = $args;
			$value              = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
			switch ( $type ) {
				case 'text':
				case 'email': // Though not used in current fields, good to keep for consistency
				case 'url':
				case 'date':  // Though not used in current fields
				case 'number':
					echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
					break;
				case 'boolean':
					echo '<input type="checkbox" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . ' />';
					break;
				case 'textarea':
					echo '<textarea name="' . esc_attr( $key ) . '" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
					break;
				default:
					echo '<input type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	public static function save_artwork_meta( $post_id, $post ) {
		if ( ! isset( $_POST['ead_artwork_meta_nonce_field'] ) || ! wp_verify_nonce( $_POST['ead_artwork_meta_nonce_field'], 'ead_artwork_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( $post->post_type !== 'artpulse_artwork' ) {
			return; // Corrected CPT slug
		}

		$registered_fields = self::get_registered_artwork_meta_fields();
		foreach ( $registered_fields as $field => $args ) {
			$type      = $args[0];
			$value     = $_POST[ $field ] ?? '';
			$old_value = get_post_meta( $post_id, $field, true );

			// Basic validation examples
			if ( $type === 'url' && ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
				// Optionally add an admin notice here if validation fails
				continue;
			}
			if ( $field === 'artwork_year' && ! empty( $value ) && ! preg_match( '/^\d{4}$/', $value ) ) {
				// Optionally add an admin notice here if validation fails
				continue;
			}
			if ( $type === 'number' && ! empty( $value ) && ! is_numeric( $value ) ) {
				// Optionally add an admin notice here if validation fails
				continue;
			}

			if ( $type === 'boolean' ) {
				$value = isset( $_POST[ $field ] ) ? '1' : '0';
			} elseif ( $type === 'textarea' ) {
				$value = sanitize_textarea_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}

			if ( $field === 'artwork_price' && $value !== $old_value ) {
				$history = get_post_meta( $post_id, 'price_history', true );
				if ( ! is_array( $history ) ) {
					$history = array();
				}
				if ( $old_value !== '' ) {
					$history[] = array(
						'price' => $old_value,
						'date'  => current_time( 'mysql' ),
					);
				}
				update_post_meta( $post_id, 'price_history', $history );
			}

			update_post_meta( $post_id, $field, $value );
		}

		$medium_value = get_post_meta( $post_id, 'artwork_medium', true );
		$medium_terms = array_filter( array_map( 'trim', explode( ',', $medium_value ) ) );
		$medium_ids   = array();
		foreach ( $medium_terms as $name ) {
			$term = term_exists( $name, 'artpulse_medium' );
			if ( ! $term ) {
				$term = wp_insert_term( $name, 'artpulse_medium' );
			}
			if ( ! is_wp_error( $term ) ) {
				$medium_ids[] = (int) ( $term['term_id'] ?? $term );
			}
		}
		if ( $medium_ids ) {
			wp_set_object_terms( $post_id, $medium_ids, 'artpulse_medium', false );
		} else {
			wp_set_object_terms( $post_id, array(), 'artpulse_medium', false );
		}

		$style_value = get_post_meta( $post_id, 'artwork_styles', true );
		$style_terms = array_filter( array_map( 'trim', explode( ',', $style_value ) ) );
		$style_ids   = array();
		foreach ( $style_terms as $name ) {
			$term = term_exists( $name, 'artwork_style' );
			if ( ! $term ) {
				$term = wp_insert_term( $name, 'artwork_style' );
			}
			if ( ! is_wp_error( $term ) ) {
				$style_ids[] = (int) ( $term['term_id'] ?? $term );
			}
		}
		if ( $style_ids ) {
			wp_set_object_terms( $post_id, $style_ids, 'artwork_style', false );
		} else {
			wp_set_object_terms( $post_id, array(), 'artwork_style', false );
		}
	}

	public static function register_rest_fields() {
		foreach ( self::get_registered_artwork_meta_fields() as $field => $args ) {
			register_rest_field(
				'artpulse_artwork',
				$field,
				array( // Corrected CPT slug
					'get_callback'    => function ( $object ) use ( $field ) {
						return get_post_meta( $object['id'], $field, true );
					},
					'update_callback' => function ( $value, $object ) use ( $field ) {
						// Consider adding validation similar to save_artwork_meta here
						$field_type = self::get_registered_artwork_meta_fields()[ $field ][0] ?? 'text';
						if ( $field_type === 'boolean' ) {
							$sanitized_value = $value ? '1' : '0';
						} elseif ( $field_type === 'textarea' ) {
							$sanitized_value = sanitize_textarea_field( $value );
						} else {
							$sanitized_value = sanitize_text_field( $value );
						}
						return update_post_meta( $object->ID, $field, $sanitized_value );
					},
					'schema'          => array(
						'type'    => $args[0] === 'boolean' ? 'boolean' : ( $args[0] === 'number' ? 'integer' : 'string' ),
						'context' => array( 'view', 'edit' ),
					),
				)
			);
		}
	}

	public static function add_admin_filters() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'artpulse_artwork' ) {
			return; // Corrected CPT slug
		}

		$selected = $_GET['artwork_featured'] ?? '';
		echo '<select name="artwork_featured">
            <option value="">' . __( 'Filter by Featured', 'artpulse' ) . '</option>
            <option value="1"' . selected( $selected, '1', false ) . '>Yes</option>
            <option value="0"' . selected( $selected, '0', false ) . '>No</option>
        </select>';
	}

	public static function filter_artworks_admin_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'artpulse_artwork' ) {
			return; // Corrected CPT slug
		}

		if ( isset( $_GET['artwork_featured'] ) && $_GET['artwork_featured'] !== '' ) {
			$query->set( 'meta_key', 'artwork_featured' );
			$query->set( 'meta_value', $_GET['artwork_featured'] );
		}
	}

	private static function get_registered_artwork_meta_fields() {
		return array(
			'artwork_title'           => array( 'text', __( 'Title of the artwork', 'artpulse' ) ),
			// 'artwork_artist' field is better handled by MetaBoxesRelationship
			'artwork_medium'          => array( 'text', __( 'Medium used (e.g. oil on canvas)', 'artpulse' ) ),
			'artwork_dimensions'      => array( 'text', __( 'Dimensions (e.g. 100x120cm)', 'artpulse' ) ),
			'artwork_year'            => array( 'text', __( 'Year created (YYYY)', 'artpulse' ) ),
			'artwork_materials'       => array( 'textarea', __( 'List of materials', 'artpulse' ) ),
			'artwork_price'           => array( 'text', __( 'Asking price (e.g. $2000 or POA)', 'artpulse' ) ),
			'artwork_provenance'      => array( 'textarea', __( 'Provenance or exhibition history', 'artpulse' ) ),
			'artwork_edition'         => array( 'text', __( 'Edition/number (e.g. 1/10)', 'artpulse' ) ),
			'artwork_tags'            => array( 'text', __( 'Tags (comma-separated)', 'artpulse' ) ),
			'artwork_description'     => array( 'textarea', __( 'Artwork description', 'artpulse' ) ),
			'artwork_image'           => array( 'number', __( 'Featured image ID (Media Library ID)', 'artpulse' ) ), // This usually refers to the post thumbnail, consider if a separate field is needed.
			'artwork_video_url'       => array( 'url', __( 'Video URL (e.g., YouTube, Vimeo)', 'artpulse' ) ),
			'artwork_featured'        => array( 'boolean', __( 'Mark as featured', 'artpulse' ) ),
			'artwork_styles'          => array( 'text', __( 'Styles (comma-separated)', 'artpulse' ) ),
			'artwork_stock'           => array( 'number', __( 'Stock quantity', 'artpulse' ) ),
			'artwork_auction_enabled' => array( 'boolean', __( 'Enable auction', 'artpulse' ) ),
			'artwork_auction_start'   => array( 'text', __( 'Auction start (Y-m-d H:i)', 'artpulse' ) ),
			'artwork_auction_end'     => array( 'text', __( 'Auction end (Y-m-d H:i)', 'artpulse' ) ),
		);
	}
}
