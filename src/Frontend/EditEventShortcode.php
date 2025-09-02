<?php

namespace ArtPulse\Frontend;

class EditEventShortcode {

	public static function register() {
		\ArtPulse\Core\ShortcodeRegistry::register( 'ap_edit_event', 'Edit Event', array( self::class, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ap_save_event', array( self::class, 'handle_ajax' ) );
		add_action( 'wp_ajax_ap_delete_event', array( self::class, 'handle_ajax_delete' ) );
	}

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts
		);

		$post_id = intval( $atts['id'] );
		if ( ! $post_id || get_post_type( $post_id ) !== 'artpulse_event' ) {
			return '<p>Invalid event ID.</p>';
		}

		$event   = get_post( $post_id );
		$user_id = get_current_user_id();

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return '<p>You do not have permission to edit this event.</p>';
		}

		$title                = esc_attr( $event->post_title );
		$content              = esc_textarea( $event->post_content );
		$date                 = esc_attr( get_post_meta( $post_id, '_ap_event_date', true ) );
		$location             = esc_attr( get_post_meta( $post_id, '_ap_event_location', true ) );
		$start                = esc_attr( get_post_meta( $post_id, 'event_start_date', true ) );
		$end                  = esc_attr( get_post_meta( $post_id, 'event_end_date', true ) );
		$recurrence           = esc_attr( get_post_meta( $post_id, 'event_recurrence_rule', true ) );
		$venue                = esc_attr( get_post_meta( $post_id, 'venue_name', true ) );
		$street               = esc_attr( get_post_meta( $post_id, 'event_street_address', true ) );
		$country              = esc_attr( get_post_meta( $post_id, 'event_country', true ) );
		$state                = esc_attr( get_post_meta( $post_id, 'event_state', true ) );
		$city                 = esc_attr( get_post_meta( $post_id, 'event_city', true ) );
		$postcode             = esc_attr( get_post_meta( $post_id, 'event_postcode', true ) );
		$lat                  = esc_attr( get_post_meta( $post_id, 'event_lat', true ) );
		$lng                  = esc_attr( get_post_meta( $post_id, 'event_lng', true ) );
		$addr_comp            = esc_attr( get_post_meta( $post_id, 'address_components', true ) );
		$org_name             = esc_attr( get_post_meta( $post_id, 'event_organizer_name', true ) );
		$org_email            = esc_attr( get_post_meta( $post_id, 'event_organizer_email', true ) );
		$org_selected         = intval( get_post_meta( $post_id, '_ap_event_organization', true ) );
		$artist_ids           = (array) get_post_meta( $post_id, '_ap_event_artists', true );
		$featured_checked     = get_post_meta( $post_id, 'event_featured', true ) === '1' ? 'checked' : '';
		$rsvp_enabled_checked = get_post_meta( $post_id, 'event_rsvp_enabled', true ) === '1' ? 'checked' : '';
		$rsvp_limit           = esc_attr( get_post_meta( $post_id, 'event_rsvp_limit', true ) );
		$waitlist_checked     = get_post_meta( $post_id, 'event_waitlist_enabled', true ) === '1' ? 'checked' : '';
		$event_type           = wp_get_post_terms( $post_id, 'event_type', array( 'fields' => 'ids' ) );
		$event_type_id        = ! empty( $event_type ) ? $event_type[0] : '';

		$orgs = get_posts(
			array(
				'post_type'   => 'artpulse_org',
				'author'      => $user_id,
				'numberposts' => -1,
			)
		);

		$artists = get_posts(
			array(
				'post_type'   => 'artpulse_artist',
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		wp_enqueue_script(
			'chart-js',
			plugins_url( 'assets/libs/chart.js/4.4.1/chart.min.js', ARTPULSE_PLUGIN_FILE ),
			array(),
			null,
			true
		);
		$script_path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . 'assets/js/ap-rsvp-analytics.js';
		wp_enqueue_script(
			'ap-rsvp-analytics',
			plugins_url( 'assets/js/ap-rsvp-analytics.js', ARTPULSE_PLUGIN_FILE ),
			array( 'chart-js' ),
			file_exists( $script_path ) ? filemtime( $script_path ) : '1.0',
			true
		);
		wp_localize_script(
			'ap-rsvp-analytics',
			'APRsvpAnalytics',
			array(
				'rest_root' => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'event_id'  => $post_id,
			)
		);

		ob_start();
		?>
		<form id="ap-edit-event-form" class="ap-form-container" enctype="multipart/form-data" data-post-id="<?php echo $post_id; ?>" data-no-ajax="true">
			<p>
				<label class="ap-form-label">Title<br>
					<input class="ap-input" type="text" name="title" value="<?php echo $title; ?>" required>
				</label>
			</p>
			<p>
				<label class="ap-form-label">Description<br>
					<textarea class="ap-input" name="content" required><?php echo $content; ?></textarea>
				</label>
			</p>
			<p>
				<label class="ap-form-label">Date<br>
					<input class="ap-input" type="date" name="date" value="<?php echo $date; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Start Date<br>
					<input class="ap-input" type="date" name="start_date" value="<?php echo $start; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">End Date<br>
					<input class="ap-input" type="date" name="end_date" value="<?php echo $end; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Start Time<br>
					<input class="ap-input" type="time" name="event_start_time" value="<?php echo esc_attr( $start_time = get_post_meta( $post_id, '_ap_event_start_time', true ) ); ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">End Time<br>
					<input class="ap-input" type="time" name="event_end_time" value="<?php echo esc_attr( $end_time_meta = get_post_meta( $post_id, '_ap_event_end_time', true ) ); ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Recurrence Rule (iCal)<br>
					<input class="ap-input" type="text" name="event_recurrence_rule" value="<?php echo $recurrence; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Venue Name<br>
					<input class="ap-input" type="text" name="venue_name" value="<?php echo $venue; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Street Address<br>
					<input class="ap-input ap-address-street" type="text" name="event_street_address" value="<?php echo $street; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Country<br>
					<input class="ap-input ap-address-country" type="text" name="event_country" value="<?php echo $country; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">State/Province<br>
					<input class="ap-input ap-address-state" type="text" name="event_state" value="<?php echo $state; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">City<br>
					<input class="ap-input ap-address-city" type="text" name="event_city" value="<?php echo $city; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Postcode<br>
					<input class="ap-input ap-address-postcode" type="text" name="event_postcode" value="<?php echo $postcode; ?>">
				</label>
			</p>
			<input type="hidden" name="address_components" id="ap_address_components" value="<?php echo $addr_comp; ?>">
			<input type="hidden" name="event_lat" id="ap_lat" value="<?php echo $lat; ?>">
			<input type="hidden" name="event_lng" id="ap_lng" value="<?php echo $lng; ?>">

			<p>
				<label class="ap-form-label">Address<br>
					<input class="ap-input" type="text" name="event_address" value="<?php echo esc_attr( get_post_meta( $post_id, '_ap_event_address', true ) ); ?>">
				</label>
			</p>

			<p>
				<label class="ap-form-label">Contact Info<br>
					<input class="ap-input" type="text" name="event_contact" value="<?php echo esc_attr( get_post_meta( $post_id, '_ap_event_contact', true ) ); ?>">
				</label>
			</p>

			<p>
				<label class="ap-form-label">RSVP URL<br>
					<input class="ap-input" type="url" name="event_rsvp_url" value="<?php echo esc_attr( get_post_meta( $post_id, '_ap_event_rsvp', true ) ); ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Location<br>
					<input class="ap-input" type="text" name="location" class="ap-google-autocomplete" value="<?php echo $location; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Event Type<br>
					<?php
					wp_dropdown_categories(
						array(
							'taxonomy'         => 'event_type',
							'name'             => 'event_type',
							'selected'         => $event_type_id,
							'show_option_none' => 'Select type',
							'hide_empty'       => false,
							'class'            => 'ap-input',
						)
					);
					?>
				</label>
			</p>
			<p>
				<label class="ap-form-label">Organizer Name<br>
					<input class="ap-input" type="text" name="event_organizer_name" value="<?php echo $org_name; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Organizer Email<br>
					<input class="ap-input" type="email" name="event_organizer_email" value="<?php echo $org_email; ?>">
				</label>
			</p>
			<p>
				<label class="ap-form-label">Organization<br>
					<select class="ap-input" name="event_org" required>
						<option value="">Select Organization</option>
						<?php foreach ( $orgs as $org ) : ?>
							<option value="<?php echo esc_attr( $org->ID ); ?>" <?php selected( $org_selected, $org->ID ); ?>><?php echo esc_html( $org->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</p>
			<p>
				<label class="ap-form-label">Co-Host Artists<br>
					<select class="ap-input" name="event_artists[]" multiple>
						<?php foreach ( $artists as $artist ) : ?>
							<option value="<?php echo esc_attr( $artist->ID ); ?>" <?php echo in_array( $artist->ID, $artist_ids, true ) ? 'selected' : ''; ?>><?php echo esc_html( $artist->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</p>
			<p>
				<label class="ap-form-label">Event Banner<br>
					<input class="ap-input" type="file" name="event_banner">
				</label>
			</p>
			<p>
				<label>
					<input class="ap-input" type="checkbox" name="event_rsvp_enabled" value="1" <?php echo $rsvp_enabled_checked; ?>> Enable RSVP
				</label>
			</p>
			<p>
				<label class="ap-form-label">RSVP Limit<br>
					<input class="ap-input" type="number" name="event_rsvp_limit" value="<?php echo $rsvp_limit; ?>">
				</label>
			</p>
			<p>
				<label>
					<input class="ap-input" type="checkbox" name="event_waitlist_enabled" value="1" <?php echo $waitlist_checked; ?>> Enable Waitlist
				</label>
			</p>
			<p>
				<label>
					<input class="ap-input" type="checkbox" name="event_featured" value="1" <?php echo $featured_checked; ?>> Request Featured
				</label>
			</p>
			<p class="ap-edit-event-error"></p>
			<p>
				<button class="ap-form-button nectar-button" type="submit">Save Changes</button>
			</p>
		</form>
		<?php if ( current_user_can( 'view_artpulse_dashboard' ) ) : ?>
			<h3><?php esc_html_e( 'RSVP Analytics', 'artpulse' ); ?></h3>
			<canvas id="ap-rsvp-analytics" height="120"></canvas>
			<p><?php esc_html_e( 'Conversion from page views:', 'artpulse' ); ?> <span id="ap-rsvp-conversion">0%</span></p>
			<p><?php esc_html_e( 'Total RSVPs:', 'artpulse' ); ?> <span id="ap-total-rsvps">0</span></p>
			<p><?php esc_html_e( 'Attended:', 'artpulse' ); ?> <span id="ap-total-attended">0</span></p>
			<p><?php esc_html_e( 'Waitlist:', 'artpulse' ); ?> <span id="ap-waitlist-count">0</span></p>
			<p><?php esc_html_e( 'Favorites:', 'artpulse' ); ?> <span id="ap-favorite-count">0</span></p>
		<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	public static function enqueue_scripts() {
               wp_enqueue_media();
               \ArtPulse\Admin\EnqueueAssets::enqueue_script_if_exists( 'ap-edit-event-js', 'assets/js/ap-edit-event.js', array( 'jquery' ) );
		wp_localize_script(
			'ap-edit-event-js',
			'APEditEvent',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ap_edit_event_nonce' ),
			)
		);

               \ArtPulse\Admin\EnqueueAssets::register_script( 'chart-js', 'assets/libs/chart.js/4.4.1/chart.min.js' );
               \ArtPulse\Admin\EnqueueAssets::register_script( 'ap-rsvp-analytics', 'assets/js/ap-rsvp-analytics.js', array( 'chart-js' ) );
	}

	public static function handle_ajax() {
		$post_id = intval( $_POST['post_id'] );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}
		check_ajax_referer( 'ap_edit_event_nonce', 'nonce' );

		$title            = sanitize_text_field( $_POST['title'] );
		$content          = sanitize_textarea_field( $_POST['content'] );
		$date             = sanitize_text_field( $_POST['date'] );
		$start_date       = sanitize_text_field( $_POST['start_date'] ?? '' );
		$end_date         = sanitize_text_field( $_POST['end_date'] ?? '' );
		$recurrence       = sanitize_text_field( $_POST['event_recurrence_rule'] ?? '' );
		$location         = sanitize_text_field( $_POST['location'] );
		$venue            = sanitize_text_field( $_POST['venue_name'] ?? '' );
		$street           = sanitize_text_field( $_POST['event_street_address'] ?? '' );
		$country          = sanitize_text_field( $_POST['event_country'] ?? '' );
		$state            = sanitize_text_field( $_POST['event_state'] ?? '' );
		$city             = sanitize_text_field( $_POST['event_city'] ?? '' );
		$postcode         = sanitize_text_field( $_POST['event_postcode'] ?? '' );
		$lat              = sanitize_text_field( $_POST['event_lat'] ?? '' );
		$lng              = sanitize_text_field( $_POST['event_lng'] ?? '' );
		$addr_json        = wp_unslash( $_POST['address_components'] ?? '' );
		$addr_comp        = json_decode( $addr_json, true );
		$address_full     = sanitize_text_field( $_POST['event_address'] ?? '' );
		$start_time       = sanitize_text_field( $_POST['event_start_time'] ?? '' );
		$end_time_meta    = sanitize_text_field( $_POST['event_end_time'] ?? '' );
		$contact_info     = sanitize_text_field( $_POST['event_contact'] ?? '' );
		$rsvp_url         = sanitize_text_field( $_POST['event_rsvp_url'] ?? '' );
		$org_name         = sanitize_text_field( $_POST['event_organizer_name'] ?? '' );
		$org_email        = sanitize_email( $_POST['event_organizer_email'] ?? '' );
		$event_org        = intval( $_POST['event_org'] ?? 0 );
		$event_artists    = isset( $_POST['event_artists'] ) ? array_map( 'intval', (array) $_POST['event_artists'] ) : array();
		$event_type       = intval( $_POST['event_type'] );
		$featured         = isset( $_POST['event_featured'] ) ? '1' : '0';
		$rsvp_enabled     = isset( $_POST['event_rsvp_enabled'] ) ? '1' : '0';
		$rsvp_limit       = isset( $_POST['event_rsvp_limit'] ) ? intval( $_POST['event_rsvp_limit'] ) : 0;
		$waitlist_enabled = isset( $_POST['event_waitlist_enabled'] ) ? '1' : '0';

		if ( ! $title || ! $content ) {
			wp_send_json_error( array( 'message' => 'Title and content are required.' ) );
		}

		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => $title,
				'post_content' => $content,
			)
		);

		update_post_meta( $post_id, '_ap_event_date', $date );
		update_post_meta( $post_id, '_ap_event_location', $location );
		update_post_meta( $post_id, 'event_start_date', $start_date );
		update_post_meta( $post_id, 'event_end_date', $end_date );
		update_post_meta( $post_id, 'event_recurrence_rule', $recurrence );
		update_post_meta( $post_id, 'venue_name', $venue );
		update_post_meta( $post_id, 'event_street_address', $street );
		update_post_meta( $post_id, 'event_country', $country );
		update_post_meta( $post_id, 'event_state', $state );
		update_post_meta( $post_id, 'event_city', $city );
		update_post_meta( $post_id, 'event_postcode', $postcode );
		update_post_meta( $post_id, 'event_lat', $lat );
		update_post_meta( $post_id, 'event_lng', $lng );
		if ( is_array( $addr_comp ) ) {
			update_post_meta( $post_id, 'address_components', wp_json_encode( $addr_comp ) );
		}
		update_post_meta( $post_id, '_ap_event_address', $address_full );
		update_post_meta( $post_id, '_ap_event_start_time', $start_time );
		update_post_meta( $post_id, '_ap_event_end_time', $end_time_meta );
		update_post_meta( $post_id, '_ap_event_contact', $contact_info );
		update_post_meta( $post_id, '_ap_event_rsvp', $rsvp_url );
		update_post_meta( $post_id, 'event_organizer_name', $org_name );
		update_post_meta( $post_id, 'event_organizer_email', $org_email );
		update_post_meta( $post_id, '_ap_event_organization', $event_org );
		update_post_meta( $post_id, '_ap_event_artists', $event_artists );
		update_post_meta( $post_id, 'event_featured', $featured );
		update_post_meta( $post_id, 'event_rsvp_enabled', $rsvp_enabled );
		update_post_meta( $post_id, 'event_rsvp_limit', $rsvp_limit );
		update_post_meta( $post_id, 'event_waitlist_enabled', $waitlist_enabled );

		if ( ! empty( $_FILES['event_banner']['name'] ) ) {
			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
			}

			$attachment_id = media_handle_upload( 'event_banner', $post_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_post_meta( $post_id, 'event_banner_id', $attachment_id );

				$images = get_post_meta( $post_id, '_ap_submission_images', true );
				if ( ! is_array( $images ) ) {
					$images = array();
				}
				array_unshift( $images, $attachment_id );
				$images = array_values( array_unique( $images ) );
				update_post_meta( $post_id, '_ap_submission_images', $images );
			}
		}

		if ( $event_type ) {
			wp_set_post_terms( $post_id, array( $event_type ), 'event_type' );
		}

		wp_send_json_success( array( 'message' => 'Event updated.' ) );
	}

	public static function handle_ajax_delete() {
		$post_id = intval( $_POST['post_id'] );
		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
		}

		check_ajax_referer( 'ap_edit_event_nonce', 'nonce' );

		if ( get_post_type( $post_id ) !== 'artpulse_event' ) {
			wp_send_json_error( array( 'message' => 'Invalid event.' ) );
		}

		wp_delete_post( $post_id, true );
		wp_send_json_success( array( 'message' => 'Deleted' ) );
	}
}
