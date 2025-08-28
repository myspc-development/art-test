<?php
namespace ArtPulse\Core;

class EventTemplateManager {

	private const EVENT_META_FIELDS = array(
		'_ap_event_date',
		'_ap_event_location',
		'event_start_date',
		'event_end_date',
		'event_recurrence_rule',
		'venue_name',
		'event_street_address',
		'event_city',
		'event_state',
		'event_country',
		'event_postcode',
		'address_components',
		'event_organizer_name',
		'event_organizer_email',
		'event_banner_id',
		'_ap_submission_images',
		'_ap_event_organization',
		'_ap_event_artists',
		'event_featured',
		'event_rsvp_enabled',
		'event_rsvp_limit',
		'event_waitlist_enabled',
		'event_rsvp_list',
		'event_waitlist',
		'event_attended',
	);

	public static function register(): void {
		add_action( 'init', array( self::class, 'register_post_type' ) );
	}

	public static function register_post_type(): void {
		register_post_type(
			'ap_event_template',
			array(
				'label'        => __( 'Event Templates', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'edit.php?post_type=artpulse_event',
				'supports'     => array( 'title', 'editor' ),
			)
		);
	}

	public static function duplicate_event( int $event_id, int $author_id = 0 ): int {
		$event = get_post( $event_id );
		if ( ! $event || $event->post_type !== 'artpulse_event' ) {
			return 0;
		}

		$new_id = wp_insert_post(
			array(
				'post_type'    => 'artpulse_event',
				'post_status'  => 'draft',
				'post_title'   => $event->post_title . ' (Copy)',
				'post_content' => $event->post_content,
				'post_author'  => $author_id ?: $event->post_author,
			)
		);
		if ( is_wp_error( $new_id ) ) {
			return 0;
		}

		foreach ( self::EVENT_META_FIELDS as $key ) {
			$val = get_post_meta( $event_id, $key, true );
			if ( $val !== '' && $val !== array() ) {
				update_post_meta( $new_id, $key, maybe_unserialize( $val ) );
			}
		}

		$terms = wp_get_object_terms( $event_id, 'event_type', array( 'fields' => 'ids' ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			wp_set_object_terms( $new_id, $terms, 'event_type' );
		}

		return (int) $new_id;
	}

	public static function save_as_template( int $event_id, int $author_id = 0 ): int {
		$event = get_post( $event_id );
		if ( ! $event || $event->post_type !== 'artpulse_event' ) {
			return 0;
		}

		$template_id = wp_insert_post(
			array(
				'post_type'    => 'ap_event_template',
				'post_status'  => 'publish',
				'post_title'   => $event->post_title,
				'post_content' => $event->post_content,
				'post_author'  => $author_id ?: $event->post_author,
			)
		);
		if ( is_wp_error( $template_id ) ) {
			return 0;
		}

		$data = array();
		foreach ( self::EVENT_META_FIELDS as $key ) {
			$data['meta'][ $key ] = get_post_meta( $event_id, $key, true );
		}
		$terms = wp_get_object_terms( $event_id, 'event_type', array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $terms ) ) {
			$data['terms'] = $terms;
		}
		update_post_meta( $template_id, '_ap_template_data', $data );

		return (int) $template_id;
	}

	public static function create_from_template( int $template_id, int $author_id = 0 ): int {
		$template = get_post( $template_id );
		if ( ! $template || $template->post_type !== 'ap_event_template' ) {
			return 0;
		}

		$data = get_post_meta( $template_id, '_ap_template_data', true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$event_id = wp_insert_post(
			array(
				'post_type'    => 'artpulse_event',
				'post_status'  => 'draft',
				'post_title'   => $template->post_title,
				'post_content' => $template->post_content,
				'post_author'  => $author_id ?: $template->post_author,
			)
		);
		if ( is_wp_error( $event_id ) ) {
			return 0;
		}

		foreach ( ( $data['meta'] ?? array() ) as $key => $val ) {
			if ( $val !== '' && $val !== array() ) {
				update_post_meta( $event_id, $key, $val );
			}
		}

		if ( ! empty( $data['terms'] ) ) {
			wp_set_object_terms( $event_id, $data['terms'], 'event_type' );
		}

		return (int) $event_id;
	}

	public static function get_user_templates( int $user_id ): array {
		return get_posts(
			array(
				'post_type'   => 'ap_event_template',
				'post_status' => 'publish',
				'author'      => $user_id,
				'numberposts' => -1,
			)
		);
	}
}
