<?php
namespace ArtPulse\Frontend;

use ArtPulse\Admin\MetaBoxesOrganisation;

class OrgProfileEditShortcode {
	public static function register() {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_org_profile_edit', 'Edit Organization Profile', array( self::class, 'render_form' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
		self::handle_form_submission();
	}

	public static function enqueue_styles() {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			add_filter( 'ap_bypass_shortcode_detection', '__return_true' );
			ap_enqueue_global_styles();
		}
	}

	public static function render_form() {
		if ( ! is_user_logged_in() ) {
			return '<p>You must be logged in to edit your organization.</p>';
		}

		$user_id = get_current_user_id();
		$org_id  = get_user_meta( $user_id, 'ap_organization_id', true );
		if ( ! $org_id ) {
			return '<p>No organization assigned.</p>';
		}

		$org_post = get_post( $org_id );
		$fields   = MetaBoxesOrganisation::get_registered_org_meta_fields();
		unset( $fields['ead_org_name'], $fields['ap_org_country'], $fields['ap_org_city'] );

		$address_json = get_post_meta( $org_id, 'address_components', true );
		$components   = $address_json ? json_decode( $address_json, true ) : array();
		$country      = $components['country'] ?? '';
		$state        = $components['state'] ?? '';
		$city         = $components['city'] ?? '';

		$output = '';
		ob_start();
		?>
		<form method="post" enctype="multipart/form-data" class="ap-org-profile-edit-form ap-form-container" data-no-ajax="true">
			<?php wp_nonce_field( 'ap_org_profile_edit_action', 'ap_org_profile_nonce' ); ?>
			<div class="ap-form-messages" role="status" aria-live="polite"></div>
			<div class="form-group">
				<label class="ap-form-label" for="post_title"><?php esc_html_e( 'Organization Name', 'artpulse' ); ?></label>
				<input class="ap-input" type="text" id="post_title" name="post_title" value="<?php echo esc_attr( $org_post->post_title ); ?>" required>
			</div>
			<div class="form-group">
				<label class="ap-form-label" for="ap_org_country"><?php esc_html_e( 'Country', 'artpulse' ); ?></label>
				<input class="ap-input ap-address-country ap-address-input" id="ap_org_country" type="text" name="ap_org_country" data-selected="<?php echo esc_attr( $country ); ?>" />
			</div>
			<div class="form-group">
				<label class="ap-form-label" for="ap_org_state"><?php esc_html_e( 'State/Province', 'artpulse' ); ?></label>
				<input class="ap-input ap-address-state ap-address-input" id="ap_org_state" type="text" name="ap_org_state" data-selected="<?php echo esc_attr( $state ); ?>" />
			</div>
			<div class="form-group">
				<label class="ap-form-label" for="ap_org_city"><?php esc_html_e( 'City', 'artpulse' ); ?></label>
				<input class="ap-input ap-address-city ap-address-input" id="ap_org_city" type="text" name="ap_org_city" data-selected="<?php echo esc_attr( $city ); ?>" />
			</div>
			<input class="ap-input" type="hidden" name="address_components" id="ap-org-address-components" value="
			<?php
			echo esc_attr(
				json_encode(
					array(
						'country' => $country,
						'state'   => $state,
						'city'    => $city,
					)
				)
			);
			?>
																													">
			<?php
			foreach ( $fields as $key => $args ) {
				list($type, $label) = $args;
				$value              = get_post_meta( $org_id, $key, true );
				?>
				<div class="form-group">
					<label class="ap-form-label" for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					<?php
					switch ( $type ) {
						case 'textarea':
							echo '<textarea class="ap-input" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
							break;
						case 'checkbox':
							echo '<input class="ap-input" id="' . esc_attr( $key ) . '" type="checkbox" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . ' />';
							break;
						case 'select':
							if ( $key === 'ead_org_type' ) {
								$opts = array( 'gallery', 'museum', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other' );
								echo '<select class="ap-input" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
								echo '<option value="">' . esc_html__( 'Select', 'artpulse' ) . '</option>';
								foreach ( $opts as $opt ) {
									echo '<option value="' . esc_attr( $opt ) . '" ' . selected( $value, $opt, false ) . '>' . esc_html( ucfirst( str_replace( '-', ' ', $opt ) ) ) . '</option>';
								}
								echo '</select>';
							} else {
								echo '<input class="ap-input" id="' . esc_attr( $key ) . '" type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
							}
							break;
						case 'media':
							$img = $value ? wp_get_attachment_url( $value ) : '';
							if ( $img ) {
								echo '<img src="' . esc_url( $img ) . '" alt="" class="ap-image-preview" />';
							}
							echo '<input class="ap-input" id="' . esc_attr( $key ) . '" type="file" name="' . esc_attr( $key ) . '" accept="image/*" />';
							break;
						case 'email':
							echo '<input class="ap-input" id="' . esc_attr( $key ) . '" type="email" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
							break;
						case 'url':
							echo '<input class="ap-input" id="' . esc_attr( $key ) . '" type="url" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
							break;
						default:
							echo '<input class="ap-input" id="' . esc_attr( $key ) . '" type="' . esc_attr( $type ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
					}
					?>
				</div>
			<?php } ?>
			<div class="form-group">
				<input class="ap-input" type="submit" name="ap_org_profile_submit" value="<?php esc_attr_e( 'Update Organization', 'artpulse' ); ?>">
			</div>
		</form>
		<?php
		return $output . ob_get_clean();
	}

	public static function handle_form_submission() {
		if ( ! isset( $_POST['ap_org_profile_submit'] ) || ! is_user_logged_in() ) {
			return;
		}

		if ( ! isset( $_POST['ap_org_profile_nonce'] ) || ! wp_verify_nonce( $_POST['ap_org_profile_nonce'], 'ap_org_profile_edit_action' ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$org_id  = get_user_meta( $user_id, 'ap_organization_id', true );
		if ( ! $org_id ) {
			return;
		}

		$title = sanitize_text_field( $_POST['post_title'] ?? '' );
		wp_update_post(
			array(
				'ID'         => $org_id,
				'post_title' => $title,
			)
		);
		update_post_meta( $org_id, 'ead_org_name', $title );

		$fields = MetaBoxesOrganisation::get_registered_org_meta_fields();
		unset( $fields['ead_org_name'], $fields['ap_org_country'], $fields['ap_org_city'] );

		if ( ! empty( $_POST['address_components'] ) ) {
			$decoded = json_decode( stripslashes( $_POST['address_components'] ), true );
			if ( is_array( $decoded ) ) {
				update_post_meta(
					$org_id,
					'address_components',
					wp_json_encode(
						array(
							'country' => sanitize_text_field( $decoded['country'] ?? '' ),
							'state'   => sanitize_text_field( $decoded['state'] ?? '' ),
							'city'    => sanitize_text_field( $decoded['city'] ?? '' ),
						)
					)
				);
			}
		}

		$country = sanitize_text_field( $_POST['ap_org_country'] ?? '' );
		$city    = sanitize_text_field( $_POST['ap_org_city'] ?? '' );
		update_post_meta( $org_id, 'ap_org_country', $country );
		update_post_meta( $org_id, 'ap_org_city', $city );

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		foreach ( $fields as $key => $args ) {
			$type = $args[0];
			if ( $type === 'media' ) {
				if ( ! empty( $_FILES[ $key ]['tmp_name'] ) ) {
					$uploaded = media_handle_upload( $key, 0 );
					if ( ! is_wp_error( $uploaded ) ) {
						update_post_meta( $org_id, $key, $uploaded );
					}
				} elseif ( isset( $_POST[ $key ] ) && is_numeric( $_POST[ $key ] ) ) {
					update_post_meta( $org_id, $key, intval( $_POST[ $key ] ) );
				}
				continue;
			}

			$value = $_POST[ $key ] ?? '';
			if ( $type === 'textarea' ) {
				$san = sanitize_textarea_field( $value );
			} elseif ( $type === 'url' ) {
				$san = esc_url_raw( $value );
			} elseif ( $type === 'email' ) {
				$san = sanitize_email( $value );
			} elseif ( $type === 'checkbox' ) {
				$san = isset( $_POST[ $key ] ) ? '1' : '0';
			} else {
				$san = sanitize_text_field( $value );
			}
			update_post_meta( $org_id, $key, $san );
		}

		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( 'Organization updated successfully.', 'success' );
		}
	}
}
