<?php
namespace ArtPulse\Admin;

class MetaBoxesOrganisation {

	public static function register() {
		add_action( 'add_meta_boxes', array( self::class, 'add_org_meta_boxes' ) );
		add_action( 'save_post_artpulse_org', array( self::class, 'save_org_meta' ), 10, 2 ); // Corrected CPT slug
		add_action( 'rest_api_init', array( self::class, 'register_rest_fields' ) );
		add_action( 'restrict_manage_posts', array( self::class, 'add_admin_filters' ) );
		add_filter( 'pre_get_posts', array( self::class, 'filter_admin_query' ) );
	}

	public static function add_org_meta_boxes() {
		add_meta_box(
			'ead_org_details', // This is the meta box ID, can remain or change for consistency
			__( 'Organization Details', 'artpulse' ),
			array( self::class, 'render_org_details' ),
			'artpulse_org', // Corrected CPT slug
			'normal',
			'high'
		);
	}

	public static function render_org_details( $post ) {
		wp_nonce_field( 'ead_org_meta_nonce', 'ead_org_meta_nonce_field' );

		$fields = self::get_registered_org_meta_fields();

		echo '<table class="form-table">';
		foreach ( $fields as $key => $args ) {
			list($type, $label) = $args;
			$value              = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
			switch ( $type ) {
				case 'text':
				case 'url':
				case 'email':
				case 'date':
				case 'number':
				case 'time':
					echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
					break;
				case 'textarea':
					echo '<textarea name="' . esc_attr( $key ) . '" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . ' />';
					break;
				case 'media':
					echo '<input type="number" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="' . __( 'Media Library ID', 'artpulse' ) . '" />';
					break;
				case 'select':
					$options = array();
					if ( $key === 'ead_org_type' ) {
						$options = array(
							'gallery',
							'museum',
							'art-fair',
							'studio',
							'collective',
							'non-profit',
							'commercial-gallery',
							'public-art-space',
							'educational-institution',
							'other',
						);
					} elseif ( $key === 'ead_org_size' ) {
						$options = array( 'small', 'medium', 'large' );
					}

					echo '<select name="' . esc_attr( $key ) . '">';
					foreach ( $options as $option ) {
						echo '<option value="' . esc_attr( $option ) . '" ' . selected( $value, $option, false ) . '>' . ucfirst( str_replace( '-', ' ', $option ) ) . '</option>';
					}
					echo '</select>';
					break;
				default:
					echo '<input type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}

	public static function save_org_meta( $post_id, $post ) {
		if ( ! isset( $_POST['ead_org_meta_nonce_field'] ) || ! wp_verify_nonce( $_POST['ead_org_meta_nonce_field'], 'ead_org_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( $post->post_type !== 'artpulse_org' ) {
			return; // Corrected CPT slug
		}

		$fields = self::get_registered_org_meta_fields();
		foreach ( $fields as $field => $args ) {
			$value = isset( $_POST[ $field ] ) ? $_POST[ $field ] : '';
			$type  = $args[0];

			// Validation based on type
			if ( $type === 'url' && ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
				// Optionally add admin notice
				continue;
			}
			if ( ( $field === 'ead_org_geo_lat' || $field === 'ead_org_geo_lng' ) && ! empty( $value ) && ! is_numeric( $value ) ) {
				// Optionally add admin notice
				continue;
			}

			// Sanitize based on type
			if ( $type === 'textarea' ) {
				$sanitized_value = sanitize_textarea_field( $value );
			} elseif ( $type === 'url' ) {
				$sanitized_value = esc_url_raw( $value );
			} elseif ( $type === 'checkbox' ) {
				$sanitized_value = isset( $_POST[ $field ] ) ? '1' : '0';
			} else {
				$sanitized_value = sanitize_text_field( $value );
			}

			if ( $type === 'media' && ! empty( $sanitized_value ) && ! is_numeric( $sanitized_value ) ) {
				continue;
			}

			update_post_meta( $post_id, $field, $sanitized_value );
		}
	}

	// The original validate_field method was a bit broad. It's better to validate within save_org_meta.
	// private static function validate_field($field, $value) { ... }

	public static function register_rest_fields() {
		foreach ( self::get_registered_org_meta_fields() as $key => $args ) { // Iterate over the fields array
			register_rest_field(
				'artpulse_org',
				$key,
				array( // Corrected CPT slug
					'get_callback'    => fn( $data ) => get_post_meta( $data['id'], $key, true ),
					'update_callback' => function ( $value, $object ) use ( $key, $args ) {
						$type            = $args[0];
						$sanitized_value = '';
						if ( $type === 'textarea' ) {
							$sanitized_value = sanitize_textarea_field( $value );
						} elseif ( $type === 'url' ) {
							$sanitized_value = esc_url_raw( $value );
						} else {
							$sanitized_value = sanitize_text_field( $value );
						}
						return update_post_meta( $object->ID, $key, $sanitized_value );
					},
					'schema'          => array( 'type' => $args[0] === 'checkbox' ? 'boolean' : ( $args[0] === 'media' ? 'integer' : 'string' ) ),
				)
			);
		}
	}

	public static function add_admin_filters() {
		global $typenow; // Using global $typenow is fine here
		if ( $typenow !== 'artpulse_org' ) {
			return; // Corrected CPT slug
		}

		$selected = $_GET['ead_org_type'] ?? ''; // Keep meta key for filter if it's already in use
		echo '<select name="ead_org_type">';
		echo '<option value="">' . __( 'Filter by Type', 'artpulse' ) . '</option>';
		// These types should ideally come from a dynamic source or a constant
		foreach ( array( 'gallery', 'museum', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other' ) as $type ) {
			echo '<option value="' . esc_attr( $type ) . '" ' . selected( $selected, $type, false ) . '>' . ucfirst( str_replace( '-', ' ', $type ) ) . '</option>';
		}
		echo '</select>';
	}

	public static function filter_admin_query( $query ) {
		global $pagenow;
		// Check if it's the main query on an admin edit.php page for the correct post type
		if ( ! is_admin() || $pagenow !== 'edit.php' || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'artpulse_org' ) {
			return; // Corrected CPT slug
		}

		if ( ! empty( $_GET['ead_org_type'] ) ) { // Keep meta key for filter if it's already in use
			$query->set( 'meta_key', 'ead_org_type' );
			$query->set( 'meta_value', sanitize_text_field( $_GET['ead_org_type'] ) );
		}
	}

	public static function get_registered_org_meta_fields() {
		// Note: Address fields are handled by MetaBoxesAddress.php
		return array(
			'ead_org_name'                  => array( 'text', __( 'Organization Name', 'artpulse' ) ),
			'ead_org_description'           => array( 'textarea', __( 'Description', 'artpulse' ) ),
			'ead_org_website_url'           => array( 'url', __( 'Website URL', 'artpulse' ) ),
			'ead_org_logo_id'               => array( 'media', __( 'Logo', 'artpulse' ) ),
			'ead_org_banner_id'             => array( 'media', __( 'Banner', 'artpulse' ) ),
			'ap_org_tagline'                => array( 'text', __( 'Tagline', 'artpulse' ) ),
			'ap_org_theme_color'            => array( 'text', __( 'Theme Color', 'artpulse' ) ),
			'ap_org_profile_published'      => array( 'checkbox', __( 'Make Profile Public', 'artpulse' ) ),
			'ap_org_featured_events'        => array( 'text', __( 'Featured Event IDs', 'artpulse' ) ),
			'ead_org_type'                  => array( 'select', __( 'Organization Type', 'artpulse' ) ),
			'ead_org_size'                  => array( 'select', __( 'Organization Size', 'artpulse' ) ),
			'ead_org_facebook_url'          => array( 'url', __( 'Facebook URL', 'artpulse' ) ),
			'ead_org_twitter_url'           => array( 'url', __( 'Twitter URL', 'artpulse' ) ),
			'ead_org_instagram_url'         => array( 'url', __( 'Instagram URL', 'artpulse' ) ),
			'ead_org_linkedin_url'          => array( 'url', __( 'LinkedIn URL', 'artpulse' ) ),
			'ead_org_artsy_url'             => array( 'url', __( 'Artsy URL', 'artpulse' ) ),
			'ead_org_pinterest_url'         => array( 'url', __( 'Pinterest URL', 'artpulse' ) ),
			'ead_org_youtube_url'           => array( 'url', __( 'YouTube URL', 'artpulse' ) ),
			'ead_org_primary_contact_name'  => array( 'text', __( 'Primary Contact Name', 'artpulse' ) ),
			'ead_org_primary_contact_email' => array( 'email', __( 'Primary Contact Email', 'artpulse' ) ),
			'ead_org_primary_contact_phone' => array( 'text', __( 'Primary Contact Phone', 'artpulse' ) ),
			'ead_org_primary_contact_role'  => array( 'text', __( 'Primary Contact Role', 'artpulse' ) ),
			'ap_org_country'                => array( 'text', __( 'Country', 'artpulse' ) ),
			'ap_org_city'                   => array( 'text', __( 'City', 'artpulse' ) ),
			'ead_org_street_address'        => array( 'text', __( 'Street Address', 'artpulse' ) ),
			'ead_org_postal_address'        => array( 'text', __( 'Postal Address', 'artpulse' ) ),
			'ead_org_venue_address'         => array( 'text', __( 'Venue Address', 'artpulse' ) ),
			'ead_org_venue_email'           => array( 'email', __( 'Venue Email', 'artpulse' ) ),
			'ead_org_venue_phone'           => array( 'text', __( 'Venue Phone', 'artpulse' ) ),
			// Opening hours
			'ead_org_monday_start_time'     => array( 'time', __( 'Monday Opening Time', 'artpulse' ) ),
			'ead_org_monday_end_time'       => array( 'time', __( 'Monday Closing Time', 'artpulse' ) ),
			'ead_org_monday_closed'         => array( 'checkbox', __( 'Closed on Monday', 'artpulse' ) ),
			'ead_org_tuesday_start_time'    => array( 'time', __( 'Tuesday Opening Time', 'artpulse' ) ),
			'ead_org_tuesday_end_time'      => array( 'time', __( 'Tuesday Closing Time', 'artpulse' ) ),
			'ead_org_tuesday_closed'        => array( 'checkbox', __( 'Closed on Tuesday', 'artpulse' ) ),
			'ead_org_wednesday_start_time'  => array( 'time', __( 'Wednesday Opening Time', 'artpulse' ) ),
			'ead_org_wednesday_end_time'    => array( 'time', __( 'Wednesday Closing Time', 'artpulse' ) ),
			'ead_org_wednesday_closed'      => array( 'checkbox', __( 'Closed on Wednesday', 'artpulse' ) ),
			'ead_org_thursday_start_time'   => array( 'time', __( 'Thursday Opening Time', 'artpulse' ) ),
			'ead_org_thursday_end_time'     => array( 'time', __( 'Thursday Closing Time', 'artpulse' ) ),
			'ead_org_thursday_closed'       => array( 'checkbox', __( 'Closed on Thursday', 'artpulse' ) ),
			'ead_org_friday_start_time'     => array( 'time', __( 'Friday Opening Time', 'artpulse' ) ),
			'ead_org_friday_end_time'       => array( 'time', __( 'Friday Closing Time', 'artpulse' ) ),
			'ead_org_friday_closed'         => array( 'checkbox', __( 'Closed on Friday', 'artpulse' ) ),
			'ead_org_saturday_start_time'   => array( 'time', __( 'Saturday Opening Time', 'artpulse' ) ),
			'ead_org_saturday_end_time'     => array( 'time', __( 'Saturday Closing Time', 'artpulse' ) ),
			'ead_org_saturday_closed'       => array( 'checkbox', __( 'Closed on Saturday', 'artpulse' ) ),
			'ead_org_sunday_start_time'     => array( 'time', __( 'Sunday Opening Time', 'artpulse' ) ),
			'ead_org_sunday_end_time'       => array( 'time', __( 'Sunday Closing Time', 'artpulse' ) ),
			'ead_org_sunday_closed'         => array( 'checkbox', __( 'Closed on Sunday', 'artpulse' ) ),
		);
	}
}
