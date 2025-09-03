<?php

namespace ArtPulse\Frontend;

class OrganizationDashboardShortcode {
	public static function register() {
		// Dashboard shortcode removed
		// Legacy AJAX endpoints remain for backwards compatibility
		add_action( 'wp_ajax_ap_add_org_event', array( self::class, 'handle_ajax_add_event' ) );
		add_action( 'wp_ajax_ap_get_org_event', array( self::class, 'handle_ajax_get_event' ) );
		add_action( 'wp_ajax_ap_update_org_event', array( self::class, 'handle_ajax_update_event' ) );
		add_action( 'wp_ajax_ap_delete_org_event', array( self::class, 'handle_ajax_delete_event' ) );
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_styles' ) );
	}

	public static function enqueue_styles(): void {
		if ( function_exists( 'ap_enqueue_global_styles' ) ) {
			ap_enqueue_global_styles();
		}
		wp_enqueue_script( 'ap-messages-js' );
	}

	/**
	 * Return artwork posts grouped by project stage for an organization.
	 */
	public static function get_project_stage_groups( int $org_id ): array {
		$groups = array();

		$terms = get_terms(
			array(
				'taxonomy'   => 'ap_project_stage',
				'hide_empty' => false,
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$groups[ $term->slug ] = array(
					'slug'  => $term->slug,
					'name'  => $term->name,
					'items' => array(),
				);
			}
		}

		$posts = get_posts(
			array(
				'post_type'   => 'artpulse_artwork',
				'post_status' => array( 'publish', 'pending', 'draft' ),
				'numberposts' => -1,
				'meta_key'    => 'org_id',
				'meta_value'  => $org_id,
			)
		);

		foreach ( $posts as $p ) {
			$stage_terms = get_the_terms( $p->ID, 'ap_project_stage' );
			$slug        = '';
			$name        = '';
			if ( $stage_terms && ! is_wp_error( $stage_terms ) ) {
				$slug = $stage_terms[0]->slug;
				$name = $stage_terms[0]->name;
			}

			if ( ! isset( $groups[ $slug ] ) ) {
				$groups[ $slug ] = array(
					'slug'  => $slug,
					'name'  => $name ?: __( 'Uncategorized', 'artpulse' ),
					'items' => array(),
				);
			}

			$groups[ $slug ]['items'][] = array(
				'id'    => $p->ID,
				'title' => $p->post_title,
			);
		}

		return array_values( $groups );
	}

	/**
	 * Build the markup for a single event list item with RSVP counts
	 * and action buttons.
	 *
	 * @param \WP_Post|array|object $event Event data.
	 */
	protected static function build_event_list_item( $event ): string {
		$event      = (object) $event;
		$rsvps      = get_post_meta( $event->ID, 'event_rsvp_list', true );
		$waitlist   = get_post_meta( $event->ID, 'event_waitlist', true );
		$limit      = intval( get_post_meta( $event->ID, 'event_rsvp_limit', true ) );
		$rsvp_count = is_array( $rsvps ) ? count( $rsvps ) : 0;
		$wait_count = is_array( $waitlist ) ? count( $waitlist ) : 0;

		ob_start();
		echo '<li data-event="' . esc_attr( $event->ID ) . '">' . esc_html( $event->post_title );
		if ( current_user_can( 'edit_post', $event->ID ) ) {
			echo ' <span class="ap-rsvp-count">(' . esc_html( $rsvp_count ) . '/' . esc_html( $limit ?: '&infin;' ) . ')</span>';
			if ( $wait_count ) {
				echo ' <span class="ap-waitlist-count">' . intval( $wait_count ) . ' WL</span>';
			}
			echo ' <a href="#" class="ap-view-attendees" data-id="' . esc_attr( $event->ID ) . '">Attendees</a>';
			echo ' <button class="ap-config-rsvp" data-id="' . esc_attr( $event->ID ) . '">Configure RSVP</button>';
		}
		echo ' <a href="#" class="ap-inline-edit" data-id="' . esc_attr( $event->ID ) . '">Edit</a>';
		echo ' <button class="ap-delete-event" data-id="' . esc_attr( $event->ID ) . '">Delete</button></li>';
		return ob_get_clean();
	}
	public static function render( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . esc_html__( 'You must be logged in to view this dashboard.', 'artpulse' ) . '</p>';
		}
		if ( ! current_user_can( 'organization' ) && ! current_user_can( 'administrator' ) ) {
			return '<p>' . esc_html__( 'Access denied.', 'artpulse' ) . '</p>';
		}
		$mode = ap_get_ui_mode();
		if ( $mode === 'react' ) {
			return do_shortcode( '[ap_render_ui]' );
		}
		$tag = apply_filters( 'ap_dashboard_shortcode_tag', 'ap_user_dashboard' );
		return do_shortcode( '[' . $tag . ']' );
	}


	public static function handle_ajax_add_event() {
		check_ajax_referer( 'ap_org_dashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'create_artpulse_events' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
		}

		$title              = sanitize_text_field( $_POST['ap_event_title'] );
		$date               = sanitize_text_field( $_POST['ap_event_date'] );
		$start_date         = sanitize_text_field( $_POST['ap_event_start_date'] ?? '' );
		$end_date           = sanitize_text_field( $_POST['ap_event_end_date'] ?? '' );
		$location           = sanitize_text_field( $_POST['ap_event_location'] );
		$venue_name         = sanitize_text_field( $_POST['ap_venue_name'] ?? '' );
		$street             = sanitize_text_field( $_POST['ap_event_street_address'] ?? '' );
		$country            = sanitize_text_field( $_POST['ap_event_country'] ?? '' );
		$state              = sanitize_text_field( $_POST['ap_event_state'] ?? '' );
		$city               = sanitize_text_field( $_POST['ap_event_city'] ?? '' );
		$postcode           = sanitize_text_field( $_POST['ap_event_postcode'] ?? '' );
		$address_json       = wp_unslash( $_POST['address_components'] ?? '' );
		$address_components = json_decode( $address_json, true );
		$address_full       = sanitize_text_field( $_POST['ap_event_address'] ?? '' );
		$start_time         = sanitize_text_field( $_POST['ap_event_start_time'] ?? '' );
		$end_time           = sanitize_text_field( $_POST['ap_event_end_time'] ?? '' );
		$contact_info       = sanitize_text_field( $_POST['ap_event_contact'] ?? '' );
		$rsvp_url           = sanitize_text_field( $_POST['ap_event_rsvp_url'] ?? '' );
		$organizer_name     = sanitize_text_field( $_POST['ap_event_organizer_name'] ?? '' );
		$organizer_email    = sanitize_email( $_POST['ap_event_organizer_email'] ?? '' );
		$event_type         = intval( $_POST['ap_event_type'] ?? 0 );
		$featured           = isset( $_POST['ap_event_featured'] ) ? '1' : '0';
		$org_id             = intval( $_POST['ap_event_organization'] );

		$data = array(
			'title'              => $title,
			'date'               => $date,
			'start_date'         => $start_date,
			'end_date'           => $end_date,
			'location'           => $location,
			'venue_name'         => $venue_name,
			'street'             => $street,
			'country'            => $country,
			'state'              => $state,
			'city'               => $city,
			'postcode'           => $postcode,
			'address_components' => $address_components ?: $address_json,
			'address_full'       => $address_full,
			'start_time'         => $start_time,
			'end_time'           => $end_time,
			'contact_info'       => $contact_info,
			'rsvp_url'           => $rsvp_url,
			'organizer_name'     => $organizer_name,
			'organizer_email'    => $organizer_email,
			'event_type'         => $event_type,
			'featured'           => $featured,
			'org_id'             => $org_id,
			'post_status'        => 'pending',
		);

		$event_id = EventService::create_event( $data, get_current_user_id() );
		if ( is_wp_error( $event_id ) ) {
			wp_send_json_error( array( 'message' => $event_id->get_error_message() ) );
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$image_ids = array();

		if ( ! empty( $_FILES['event_banner']['tmp_name'] ) ) {
			$attachment_id = media_handle_upload( 'event_banner', $event_id );
			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
			}
			$image_ids[] = $attachment_id;
		}

		for ( $i = 1; $i <= 5; $i++ ) {
			$key = 'image_' . $i;
			if ( ! empty( $_FILES[ $key ]['tmp_name'] ) ) {
				$id = media_handle_upload( $key, $event_id );
				if ( is_wp_error( $id ) ) {
					wp_send_json_error( array( 'message' => $id->get_error_message() ) );
				}
				$image_ids[] = $id;
			}
		}

		if ( $image_ids ) {
			update_post_meta( $event_id, '_ap_submission_images', $image_ids );
			update_post_meta( $event_id, 'event_banner_id', $image_ids[0] );
			set_post_thumbnail( $event_id, $image_ids[0] );
		}

		// Reload the event list
		ob_start();
		$events = get_posts(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => array( 'publish', 'pending', 'draft' ),
				'meta_key'    => '_ap_event_organization',
				'meta_value'  => $org_id,
				'numberposts' => 50,
			)
		);
		foreach ( $events as $event ) {
			echo self::build_event_list_item( $event );
		}
		$html = ob_get_clean();

		wp_send_json_success( array( 'updated_list_html' => $html ) );
	}

	public static function handle_ajax_get_event() {
		check_ajax_referer( 'ap_org_dashboard_nonce', 'nonce' );

		$event_id = intval( $_POST['event_id'] ?? 0 );
		if ( ! $event_id || get_post_type( $event_id ) !== 'artpulse_event' ) {
			wp_send_json_error( array( 'message' => 'Invalid event.' ) );
		}

		if ( ! current_user_can( 'edit_post', $event_id ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$data = array(
			'ap_event_title'            => get_the_title( $event_id ),
			'ap_event_date'             => get_post_meta( $event_id, '_ap_event_date', true ),
			'ap_event_start_date'       => get_post_meta( $event_id, 'event_start_date', true ),
			'ap_event_end_date'         => get_post_meta( $event_id, 'event_end_date', true ),
			'ap_event_location'         => get_post_meta( $event_id, '_ap_event_location', true ),
			'ap_venue_name'             => get_post_meta( $event_id, 'venue_name', true ),
			'ap_event_street_address'   => get_post_meta( $event_id, 'event_street_address', true ),
			'ap_event_country'          => get_post_meta( $event_id, 'event_country', true ),
			'ap_event_state'            => get_post_meta( $event_id, 'event_state', true ),
			'ap_event_city'             => get_post_meta( $event_id, 'event_city', true ),
			'ap_event_postcode'         => get_post_meta( $event_id, 'event_postcode', true ),
			'address_components'        => get_post_meta( $event_id, 'address_components', true ),
			'ap_event_address'          => get_post_meta( $event_id, '_ap_event_address', true ),
			'ap_event_start_time'       => get_post_meta( $event_id, '_ap_event_start_time', true ),
			'ap_event_end_time'         => get_post_meta( $event_id, '_ap_event_end_time', true ),
			'ap_event_contact'          => get_post_meta( $event_id, '_ap_event_contact', true ),
			'ap_event_rsvp_url'         => get_post_meta( $event_id, '_ap_event_rsvp', true ),
			'ap_event_organizer_name'   => get_post_meta( $event_id, 'event_organizer_name', true ),
			'ap_event_organizer_email'  => get_post_meta( $event_id, 'event_organizer_email', true ),
			'ap_event_type'             => current( $terms = wp_get_post_terms( $event_id, 'event_type', array( 'fields' => 'ids' ) ) ) ?: '',
			'ap_event_featured'         => get_post_meta( $event_id, 'event_featured', true ),
			'ap_event_rsvp_enabled'     => get_post_meta( $event_id, 'event_rsvp_enabled', true ),
			'ap_event_rsvp_limit'       => get_post_meta( $event_id, 'event_rsvp_limit', true ),
			'ap_event_waitlist_enabled' => get_post_meta( $event_id, 'event_waitlist_enabled', true ),
		);

		wp_send_json_success( $data );
	}

	public static function handle_ajax_update_event() {
		check_ajax_referer( 'ap_org_dashboard_nonce', 'nonce' );

		$event_id = intval( $_POST['ap_event_id'] ?? 0 );
		if ( ! $event_id || get_post_type( $event_id ) !== 'artpulse_event' ) {
			wp_send_json_error( array( 'message' => 'Invalid event.' ) );
		}

		if ( ! current_user_can( 'edit_post', $event_id ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$title              = sanitize_text_field( $_POST['ap_event_title'] );
		$date               = sanitize_text_field( $_POST['ap_event_date'] );
		$start_date         = sanitize_text_field( $_POST['ap_event_start_date'] ?? '' );
		$end_date           = sanitize_text_field( $_POST['ap_event_end_date'] ?? '' );
		$location           = sanitize_text_field( $_POST['ap_event_location'] );
		$venue_name         = sanitize_text_field( $_POST['ap_venue_name'] ?? '' );
		$street             = sanitize_text_field( $_POST['ap_event_street_address'] ?? '' );
		$country            = sanitize_text_field( $_POST['ap_event_country'] ?? '' );
		$state              = sanitize_text_field( $_POST['ap_event_state'] ?? '' );
		$city               = sanitize_text_field( $_POST['ap_event_city'] ?? '' );
		$postcode           = sanitize_text_field( $_POST['ap_event_postcode'] ?? '' );
		$address_json       = wp_unslash( $_POST['address_components'] ?? '' );
		$address_components = json_decode( $address_json, true );
		$address_full       = sanitize_text_field( $_POST['ap_event_address'] ?? '' );
		$start_time         = sanitize_text_field( $_POST['ap_event_start_time'] ?? '' );
		$end_time           = sanitize_text_field( $_POST['ap_event_end_time'] ?? '' );
		$contact_info       = sanitize_text_field( $_POST['ap_event_contact'] ?? '' );
		$rsvp_url           = sanitize_text_field( $_POST['ap_event_rsvp_url'] ?? '' );
		$organizer_name     = sanitize_text_field( $_POST['ap_event_organizer_name'] ?? '' );
		$organizer_email    = sanitize_email( $_POST['ap_event_organizer_email'] ?? '' );
		$event_type         = intval( $_POST['ap_event_type'] ?? 0 );
		$featured           = isset( $_POST['ap_event_featured'] ) ? '1' : '0';
		$rsvp_enabled       = isset( $_POST['ap_event_rsvp_enabled'] ) ? '1' : '0';
		$rsvp_limit         = isset( $_POST['ap_event_rsvp_limit'] ) ? intval( $_POST['ap_event_rsvp_limit'] ) : 0;
		$waitlist_enabled   = isset( $_POST['ap_event_waitlist_enabled'] ) ? '1' : '0';

		wp_update_post(
			array(
				'ID'         => $event_id,
				'post_title' => $title,
			)
		);

		update_post_meta( $event_id, '_ap_event_date', $date );
		update_post_meta( $event_id, 'event_start_date', $start_date );
		update_post_meta( $event_id, 'event_end_date', $end_date );
		update_post_meta( $event_id, '_ap_event_location', $location );
		update_post_meta( $event_id, 'venue_name', $venue_name );
		update_post_meta( $event_id, 'event_street_address', $street );
		update_post_meta( $event_id, 'event_country', $country );
		update_post_meta( $event_id, 'event_state', $state );
		update_post_meta( $event_id, 'event_city', $city );
		update_post_meta( $event_id, 'event_postcode', $postcode );
		if ( is_array( $address_components ) ) {
			update_post_meta( $event_id, 'address_components', wp_json_encode( $address_components ) );
		}
		update_post_meta( $event_id, '_ap_event_address', $address_full );
		update_post_meta( $event_id, '_ap_event_start_time', $start_time );
		update_post_meta( $event_id, '_ap_event_end_time', $end_time );
		update_post_meta( $event_id, '_ap_event_contact', $contact_info );
		update_post_meta( $event_id, '_ap_event_rsvp', $rsvp_url );
		update_post_meta( $event_id, 'event_organizer_name', $organizer_name );
		update_post_meta( $event_id, 'event_organizer_email', $organizer_email );
		update_post_meta( $event_id, 'event_featured', $featured );
		update_post_meta( $event_id, 'event_rsvp_enabled', $rsvp_enabled );
		update_post_meta( $event_id, 'event_rsvp_limit', $rsvp_limit );
		update_post_meta( $event_id, 'event_waitlist_enabled', $waitlist_enabled );

		if ( $event_type ) {
			wp_set_post_terms( $event_id, array( $event_type ), 'event_type' );
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$image_ids = get_post_meta( $event_id, '_ap_submission_images', true );
		if ( ! is_array( $image_ids ) ) {
			$image_ids = array();
		}

		if ( ! empty( $_FILES['event_banner']['tmp_name'] ) ) {
			$attachment_id = media_handle_upload( 'event_banner', $event_id );
			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
			}
			$image_ids[] = $attachment_id;
			update_post_meta( $event_id, 'event_banner_id', $attachment_id );
			set_post_thumbnail( $event_id, $attachment_id );
		}

		for ( $i = 1; $i <= 5; $i++ ) {
			$key = 'image_' . $i;
			if ( ! empty( $_FILES[ $key ]['tmp_name'] ) ) {
				$id = media_handle_upload( $key, $event_id );
				if ( is_wp_error( $id ) ) {
					wp_send_json_error( array( 'message' => $id->get_error_message() ) );
				}
				$image_ids[] = $id;
			}
		}

		if ( $image_ids ) {
			update_post_meta( $event_id, '_ap_submission_images', $image_ids );
		}

               // Reload the event list for this organization
               // Determine organization from event meta or fall back to current user's organization
               $org_id = intval( get_post_meta( $event_id, '_ap_event_organization', true ) );
               if ( ! $org_id ) {
                       $org_id = intval( get_user_meta( get_current_user_id(), 'ap_organization_id', true ) );
               }

               ob_start();
               $events = get_posts(
                       array(
                               'post_type'   => 'artpulse_event',
                               'post_status' => array( 'publish', 'pending', 'draft' ),
                               'meta_key'    => '_ap_event_organization',
                                'meta_value'  => $org_id,
                                'numberposts' => 50,
                        )
                );
		foreach ( $events as $event ) {
			echo self::build_event_list_item( $event );
		}
		$html = ob_get_clean();

		wp_send_json_success( array( 'updated_list_html' => $html ) );
	}

	public static function handle_ajax_delete_event() {
		check_ajax_referer( 'ap_org_dashboard_nonce', 'nonce' );

		if ( ! current_user_can( 'delete_post', intval( $_POST['event_id'] ?? 0 ) ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
		}

		$event_id = intval( $_POST['event_id'] ?? 0 );
		$post     = get_post( $event_id );

		if ( ! $post || get_post_type( $event_id ) !== 'artpulse_event' ) {
			wp_send_json_error( array( 'message' => 'Invalid event.' ) );
		}

		$user_id   = get_current_user_id();
		$user_org  = get_user_meta( $user_id, 'ap_organization_id', true );
		$event_org = intval( get_post_meta( $event_id, '_ap_event_organization', true ) );

		if ( ! $user_org || $user_org != $event_org ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		wp_delete_post( $event_id, true );

		// Reload the event list for this organization
		ob_start();
		$events = get_posts(
			array(
				'post_type'   => 'artpulse_event',
				'post_status' => array( 'publish', 'pending', 'draft' ),
				'meta_key'    => '_ap_event_organization',
				'meta_value'  => $user_org,
				'numberposts' => 50,
			)
		);
		foreach ( $events as $event ) {
			echo self::build_event_list_item( $event );
		}
		$html = ob_get_clean();

		wp_send_json_success( array( 'updated_list_html' => $html ) );
	}
}
