<?php

namespace ArtPulse\Core;

class PostTypeRegistrar {

	public static function register() {
		// Base config shared by all CPTs
		$common = array(
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'has_archive'  => true,
			'rewrite'      => true,
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
		);

		// Register CPTs with custom menu icons
		$post_types = array(
			'artpulse_event'      => array(
				'label'      => __( 'Events', 'artpulse' ),
				'rewrite'    => array( 'slug' => 'events' ),
				'taxonomies' => array( 'event_type' ),
				'menu_icon'  => 'dashicons-calendar',
			),
			'artpulse_artist'     => array(
				'label'     => __( 'Artists', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'artists' ),
				'supports'  => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				'menu_icon' => 'dashicons-admin-users',
			),
			'artist_profile'      => array(
				'label'     => __( 'Artist Profiles', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'artist' ),
				'supports'  => array( 'title', 'editor', 'thumbnail', 'author' ),
				'menu_icon' => 'dashicons-id',
			),
			'ap_artist_request'   => array(
				'label'        => __( 'Artist Upgrade Requests', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'supports'     => array( 'title' ),
			),
			'ap_profile_link_req' => array(
				'label'        => __( 'Profile Link Requests', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'supports'     => array( 'title' ),
				'menu_icon'    => 'dashicons-admin-links',
			),
			'ap_profile_link'     => array(
				'label'        => __( 'Profile Links', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'supports'     => array( 'title' ),
				'menu_icon'    => 'dashicons-admin-users',
			),
			'artpulse_artwork'    => array(
				'label'     => __( 'Artworks', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'artworks' ),
				'supports'  => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				'menu_icon' => 'dashicons-format-image',
			),
			'artpulse_org'        => array(
				'label'     => __( 'Organizations', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'organizations' ),
				'menu_icon' => 'dashicons-building',
			),
			'ap_event_template'   => array(
				'label'        => __( 'Event Templates', 'artpulse' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'edit.php?post_type=artpulse_event',
				'supports'     => array( 'title', 'editor' ),
				'menu_icon'    => 'dashicons-calendar-alt',
			),
			'review'              => array(
				'label'     => __( 'Reviews', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'reviews' ),
				'supports'  => array( 'title', 'editor', 'author' ),
				'menu_icon' => 'dashicons-star-filled',
			),
			'ap_collection'       => array(
				'label'     => __( 'Collections', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'collections' ),
				'supports'  => array( 'title', 'editor', 'thumbnail', 'author' ),
				'menu_icon' => 'dashicons-screenoptions',
			),
			'ap_forum_thread'     => array(
				'label'     => __( 'Forum Threads', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'forum' ),
				'supports'  => array( 'title', 'editor', 'author', 'comments' ),
				'menu_icon' => 'dashicons-format-chat',
			),
			'ap_competition'      => array(
				'label'     => __( 'Competitions', 'artpulse' ),
				'rewrite'   => array( 'slug' => 'competitions' ),
				'supports'  => array( 'title', 'editor', 'thumbnail' ),
				'menu_icon' => 'dashicons-awards',
			),
			'ap_message'          => array(
				'label'        => __( 'Messages', 'artpulse' ),
				'rewrite'      => array( 'slug' => 'messages' ),
				'supports'     => array( 'title', 'editor', 'author' ),
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-email',
			),
			'ap_news_item'        => array(
				'label'        => __( 'News Items', 'artpulse' ),
				'rewrite'      => array( 'slug' => 'news' ),
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'show_in_rest' => true,
				'public'       => true,
				'menu_icon'    => 'dashicons-megaphone',
			),
		);

		$opts              = get_option( 'artpulse_settings', array() );
		$default_rsvp      = isset( $opts['default_rsvp_limit'] ) ? absint( $opts['default_rsvp_limit'] ) : 50;
		$default_waitlists = ! empty( $opts['waitlists_enabled'] );

		foreach ( $post_types as $post_type => $args ) {
			$capabilities = self::generate_caps( $post_type );
			register_post_type(
				$post_type,
				array_merge(
					$common,
					$args,
					array(
						'capability_type' => $post_type,
						'map_meta_cap'    => true,
						'capabilities'    => $capabilities,
					)
				)
			);
		}

		// Register Meta Boxes
		self::register_meta_boxes();

		// Register additional post meta
		register_post_meta(
			'artpulse_event',
			'_ap_submission_images',
			array(
				'type'         => 'array',
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
			)
		);

		// Core event meta exposed via REST for the dashboard editor
		$event_meta = array(
			'ap_event_start'    => 'string',
			'ap_event_end'      => 'string',
			'ap_event_venue'    => 'string',
			'ap_event_address'  => 'string',
			'ap_event_lat'      => 'number',
			'ap_event_lng'      => 'number',
			'ap_event_capacity' => 'integer',
			'ap_event_price'    => 'string',
		);

		foreach ( $event_meta as $key => $type ) {
			register_post_meta(
				'artpulse_event',
				$key,
				array(
					'type'          => $type,
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => function () {
						$post_id = func_num_args() > 2 ? func_get_arg( 2 ) : null;
						return $post_id ? current_user_can( 'edit_post', $post_id ) : false;
					},
				)
			);
		}

		register_post_meta(
			'review',
			'_reviewed_id',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);

		// Taxonomies

		register_taxonomy(
			'artpulse_medium',
			'artpulse_artwork',
			array(
				'label'        => __( 'Medium', 'artpulse' ),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'medium' ),
			)
		);

		register_taxonomy(
			'art_type',
			'artpulse_artwork',
			array(
				'label'        => __( 'Art Types', 'artpulse-community' ),
				'public'       => true,
				'show_in_rest' => true,
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'art-type' ),
			)
		);

		add_action( 'init', array( self::class, 'insert_default_art_types' ), 21 );
	}

	public static function insert_default_art_types() {
		$types = array(
			'painting'     => __( 'Painting', 'artpulse-community' ),
			'drawing'      => __( 'Drawing', 'artpulse-community' ),
			'sculpture'    => __( 'Sculpture', 'artpulse-community' ),
			'installation' => __( 'Installation', 'artpulse-community' ),
			'photography'  => __( 'Photography', 'artpulse-community' ),
			'print'        => __( 'Print', 'artpulse-community' ),
			'digital-art'  => __( 'Digital Art', 'artpulse-community' ),
			'video'        => __( 'Video', 'artpulse-community' ),
			'mixed-media'  => __( 'Mixed Media', 'artpulse-community' ),
			'performance'  => __( 'Performance', 'artpulse-community' ),
			'textile'      => __( 'Textile', 'artpulse-community' ),
			'illustration' => __( 'Illustration', 'artpulse-community' ),
			'other'        => __( 'Other', 'artpulse-community' ),
		);
		foreach ( $types as $slug => $name ) {
			if ( ! term_exists( $name, 'art_type' ) ) {
				wp_insert_term( $name, 'art_type', array( 'slug' => $slug ) );
			}
		}
	}

	private static function register_meta_boxes() {
		$opts              = get_option( 'artpulse_settings', array() );
		$default_rsvp      = isset( $opts['default_rsvp_limit'] ) ? absint( $opts['default_rsvp_limit'] ) : 50;
		$default_waitlists = ! empty( $opts['waitlists_enabled'] );

		register_post_meta(
			'artpulse_event',
			'_ap_event_date',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'_ap_event_location',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_start_date',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_end_date',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_recurrence_rule',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'venue_name',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_street_address',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_city',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_state',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_country',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_postcode',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_lat',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'number',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_lng',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'number',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_organizer_name',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_organizer_email',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'_ap_event_organization',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			)
		);

		register_post_meta(
			'artpulse_event',
			'_ap_event_artists',
			array(
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'single'       => true,
				'type'         => 'array',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_banner_id',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_featured',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);

		register_post_meta(
			'artpulse_event',
			'is_featured',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);

		register_post_meta(
			'artpulse_event',
			'ap_featured',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_rsvp_enabled',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_rsvp_limit',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
				'default'      => $default_rsvp,
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_waitlist_enabled',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
				'default'      => $default_waitlists,
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_rsvp_list',
			array(
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'single'       => true,
				'type'         => 'array',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_waitlist',
			array(
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'single'       => true,
				'type'         => 'array',
			)
		);

		register_post_meta(
			'artpulse_event',
			'event_attended',
			array(
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'single'       => true,
				'type'         => 'array',
			)
		);

		register_post_meta(
			'artpulse_event',
			'_ap_virtual_event_url',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_event',
			'_ap_virtual_access_enabled',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
				'default'      => false,
			)
		);

		register_post_meta(
			'artpulse_artist',
			'_ap_artist_bio',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artist',
			'_ap_artist_org',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			)
		);

		register_post_meta(
			'artpulse_artist',
			'artist_spotlight',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
				'default'      => false,
			)
		);

		register_post_meta(
			'artpulse_artist',
			'spotlight_start_date',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artist',
			'spotlight_end_date',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'_ap_artwork_medium',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'_ap_artwork_dimensions',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'_ap_artwork_materials',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'for_sale',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'price',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'buy_link',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_artwork',
			'price_history',
			array(
				'single' => true,
				'type'   => 'array',
			)
		);

		register_post_meta(
			'artpulse_event',
			'address_components',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_org',
			'address_components',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_org',
			'ap_org_country',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'artpulse_org',
			'ap_org_city',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		// Competition fields
		register_post_meta(
			'ap_competition',
			'competition_theme',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'ap_competition',
			'competition_deadline',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'ap_competition',
			'competition_rules',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'ap_competition',
			'competition_prizes',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);

		register_post_meta(
			'ap_competition',
			'voting_method',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'default'      => 'community',
			)
		);

		register_post_meta(
			'ap_collection',
			'ap_collection_items',
			array(
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'single'       => true,
				'type'         => 'array',
			)
		);
	}

	public static function generate_caps( string $post_type ): array {
		$plural = $post_type . 's';

		return array(
			'edit_post'              => "edit_{$post_type}",
			'read_post'              => "read_{$post_type}",
			'delete_post'            => "delete_{$post_type}",
			'edit_posts'             => "edit_{$plural}",
			'edit_others_posts'      => "edit_others_{$plural}",
			'publish_posts'          => "publish_{$plural}",
			'read_private_posts'     => "read_private_{$plural}",
			'delete_posts'           => "delete_{$plural}",
			'delete_private_posts'   => "delete_private_{$plural}",
			'delete_published_posts' => "delete_published_{$plural}",
			'delete_others_posts'    => "delete_others_{$plural}",
			'edit_private_posts'     => "edit_private_{$plural}",
			'edit_published_posts'   => "edit_published_{$plural}",
			'create_posts'           => "create_{$plural}",
		);
	}
}
