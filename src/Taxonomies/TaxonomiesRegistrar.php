<?php
namespace ArtPulse\Taxonomies;

use ArtPulse\Taxonomies\SpotlightCategory;
class TaxonomiesRegistrar {
	const EVENT_TYPES_OPTION = 'ap_default_event_types_inserted';
	public static function register() {
		self::register_artist_specialties();
		self::register_artwork_styles();
		self::register_event_types();
		self::register_org_categories();
		self::register_project_stages();
				self::register_reviewed_type();
				SpotlightCategory::register();
	}
	public static function register_artist_specialties() {
		$labels = array(
			'name'          => __( 'Artist Specialties', 'artpulse' ),
			'singular_name' => __( 'Specialty', 'artpulse' ),
			'search_items'  => __( 'Search Specialties', 'artpulse' ),
			'all_items'     => __( 'All Specialties', 'artpulse' ),
			'edit_item'     => __( 'Edit Specialty', 'artpulse' ),
			'update_item'   => __( 'Update Specialty', 'artpulse' ),
			'add_new_item'  => __( 'Add New Specialty', 'artpulse' ),
			'new_item_name' => __( 'New Specialty Name', 'artpulse' ),
			'menu_name'     => __( 'Artist Specialties', 'artpulse' ),
		);
		register_taxonomy(
			'artist_specialty',
			'artpulse_artist',
			array(
				'hierarchical'      => true,
				'public'            => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'artist-specialty' ),
				'show_in_rest'      => true,
			)
		);
	}
	public static function register_artwork_styles() {
		$labels = array(
			'name'          => __( 'Artwork Styles', 'artpulse' ),
			'singular_name' => __( 'Style', 'artpulse' ),
			'search_items'  => __( 'Search Styles', 'artpulse' ),
			'all_items'     => __( 'All Styles', 'artpulse' ),
			'edit_item'     => __( 'Edit Style', 'artpulse' ),
			'update_item'   => __( 'Update Style', 'artpulse' ),
			'add_new_item'  => __( 'Add New Style', 'artpulse' ),
			'new_item_name' => __( 'New Style Name', 'artpulse' ),
			'menu_name'     => __( 'Artwork Styles', 'artpulse' ),
		);
		register_taxonomy(
			'artwork_style',
			array( 'artpulse_artwork', 'artwork', 'artpulse_artist' ),
			array(
				'hierarchical'      => true,
				'public'            => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'artwork-style' ),
				'show_in_rest'      => true,
			)
		);
	}
	public static function register_event_types() {
		$labels = array(
			'name'          => __( 'Event Types', 'artpulse' ),
			'singular_name' => __( 'Event Type', 'artpulse' ),
			'search_items'  => __( 'Search Event Types', 'artpulse' ),
			'all_items'     => __( 'All Event Types', 'artpulse' ),
			'edit_item'     => __( 'Edit Event Type', 'artpulse' ),
			'update_item'   => __( 'Update Event Type', 'artpulse' ),
			'add_new_item'  => __( 'Add New Event Type', 'artpulse' ),
			'new_item_name' => __( 'New Event Type Name', 'artpulse' ),
			'menu_name'     => __( 'Event Types', 'artpulse' ),
		);
		register_taxonomy(
			'event_type',
			array( 'artpulse_event', 'event' ),
			array(
				'hierarchical'      => true,
				'public'            => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'event-type' ),
				'show_in_rest'      => true,
			)
		);
	}
	public static function register_org_categories() {
		$labels = array(
			'name'          => __( 'Organization Categories', 'artpulse' ),
			'singular_name' => __( 'Organization Category', 'artpulse' ),
			'search_items'  => __( 'Search Organization Categories', 'artpulse' ),
			'all_items'     => __( 'All Organization Categories', 'artpulse' ),
			'edit_item'     => __( 'Edit Organization Category', 'artpulse' ),
			'update_item'   => __( 'Update Organization Category', 'artpulse' ),
			'add_new_item'  => __( 'Add New Organization Category', 'artpulse' ),
			'new_item_name' => __( 'New Organization Category Name', 'artpulse' ),
			'menu_name'     => __( 'Organization Categories', 'artpulse' ),
		);
		register_taxonomy(
			'organization_category',
			'artpulse_org',
			array(
				'hierarchical'      => true,
				'public'            => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'organization-category' ),
				'show_in_rest'      => true,
			)
		);
	}
	public static function register_project_stages() {
		$labels = array(
			'name'          => __( 'Project Stages', 'artpulse' ),
			'singular_name' => __( 'Project Stage', 'artpulse' ),
			'search_items'  => __( 'Search Stages', 'artpulse' ),
			'all_items'     => __( 'All Stages', 'artpulse' ),
			'edit_item'     => __( 'Edit Stage', 'artpulse' ),
			'update_item'   => __( 'Update Stage', 'artpulse' ),
			'add_new_item'  => __( 'Add New Stage', 'artpulse' ),
			'new_item_name' => __( 'New Stage Name', 'artpulse' ),
			'menu_name'     => __( 'Project Stages', 'artpulse' ),
		);
		register_taxonomy(
			'ap_project_stage',
			'artpulse_artwork',
			array(
				'hierarchical'      => true,
				'public'            => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'project-stage' ),
				'show_in_rest'      => true,
			)
		);
	}
	public static function register_reviewed_type() {
			$labels = array(
				'name'          => __( 'Reviewed Types', 'artpulse' ),
				'singular_name' => __( 'Reviewed Type', 'artpulse' ),
			);
			register_taxonomy(
				'reviewed_type',
				'review',
				array(
					'hierarchical' => false,
					'public'       => true,
					'labels'       => $labels,
					'show_ui'      => true,
					'show_in_rest' => true,
				)
			);
	}
	public static function maybe_insert_default_event_types() {
		if ( get_option( self::EVENT_TYPES_OPTION ) ) {
				return false;
		}
			self::insert_default_event_types();
			update_option( self::EVENT_TYPES_OPTION, 1 );
			return true;
	}
	public static function insert_default_event_types() {
			$types = array(
				'exhibition'  => __( 'Exhibition', 'artpulse' ),
				'opening'     => __( 'Opening Reception', 'artpulse' ),
				'workshop'    => __( 'Workshop', 'artpulse' ),
				'lecture'     => __( 'Lecture', 'artpulse' ),
				'performance' => __( 'Performance', 'artpulse' ),
				'screening'   => __( 'Screening', 'artpulse' ),
				'tour'        => __( 'Tour', 'artpulse' ),
				'other'       => __( 'Other', 'artpulse' ),
			);
			foreach ( $types as $slug => $name ) {
				if ( ! term_exists( $name, 'event_type' ) ) {
					wp_insert_term( $name, 'event_type', array( 'slug' => $slug ) );
				}
			}
	}
}
