<?php
namespace ArtPulse\Taxonomies;

class TaxonomiesRegistrar {
    public static function register() {
        add_action('init', [self::class, 'register_artist_specialties']);
        add_action('init', [self::class, 'register_artwork_styles']);
        add_action('init', [self::class, 'register_event_types']);
        add_action('init', [self::class, 'register_org_categories']);
        add_action('init', [self::class, 'register_project_stages']);
    }

    public static function register_artist_specialties() {
        $labels = [
            'name' => __('Artist Specialties', 'artpulse'),
            'singular_name' => __('Specialty', 'artpulse'),
            'search_items' => __('Search Specialties', 'artpulse'),
            'all_items' => __('All Specialties', 'artpulse'),
            'edit_item' => __('Edit Specialty', 'artpulse'),
            'update_item' => __('Update Specialty', 'artpulse'),
            'add_new_item' => __('Add New Specialty', 'artpulse'),
            'new_item_name' => __('New Specialty Name', 'artpulse'),
            'menu_name' => __('Artist Specialties', 'artpulse'),
        ];
        register_taxonomy('artist_specialty', 'artpulse_artist', [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'artist-specialty'],
            'show_in_rest' => true,
        ]);
    }

    public static function register_artwork_styles() {
        $labels = [
            'name' => __('Artwork Styles', 'artpulse'),
            'singular_name' => __('Style', 'artpulse'),
            'search_items' => __('Search Styles', 'artpulse'),
            'all_items' => __('All Styles', 'artpulse'),
            'edit_item' => __('Edit Style', 'artpulse'),
            'update_item' => __('Update Style', 'artpulse'),
            'add_new_item' => __('Add New Style', 'artpulse'),
            'new_item_name' => __('New Style Name', 'artpulse'),
            'menu_name' => __('Artwork Styles', 'artpulse'),
        ];
        register_taxonomy('artwork_style', 'artpulse_artwork', [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'artwork-style'],
            'show_in_rest' => true,
        ]);
    }

    public static function register_event_types() {
        $labels = [
            'name' => __('Event Types', 'artpulse'),
            'singular_name' => __('Event Type', 'artpulse'),
            'search_items' => __('Search Event Types', 'artpulse'),
            'all_items' => __('All Event Types', 'artpulse'),
            'edit_item' => __('Edit Event Type', 'artpulse'),
            'update_item' => __('Update Event Type', 'artpulse'),
            'add_new_item' => __('Add New Event Type', 'artpulse'),
            'new_item_name' => __('New Event Type Name', 'artpulse'),
            'menu_name' => __('Event Types', 'artpulse'),
        ];
        register_taxonomy('event_type', 'artpulse_event', [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'event-type'],
            'show_in_rest' => true,
        ]);
    }

    public static function register_org_categories() {
        $labels = [
            'name' => __('Organization Categories', 'artpulse'),
            'singular_name' => __('Organization Category', 'artpulse'),
            'search_items' => __('Search Organization Categories', 'artpulse'),
            'all_items' => __('All Organization Categories', 'artpulse'),
            'edit_item' => __('Edit Organization Category', 'artpulse'),
            'update_item' => __('Update Organization Category', 'artpulse'),
            'add_new_item' => __('Add New Organization Category', 'artpulse'),
            'new_item_name' => __('New Organization Category Name', 'artpulse'),
            'menu_name' => __('Organization Categories', 'artpulse'),
        ];
        register_taxonomy('organization_category', 'artpulse_org', [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'organization-category'],
            'show_in_rest' => true,
        ]);
    }

    public static function register_project_stages() {
        $labels = [
            'name' => __('Project Stages', 'artpulse'),
            'singular_name' => __('Project Stage', 'artpulse'),
            'search_items' => __('Search Stages', 'artpulse'),
            'all_items' => __('All Stages', 'artpulse'),
            'edit_item' => __('Edit Stage', 'artpulse'),
            'update_item' => __('Update Stage', 'artpulse'),
            'add_new_item' => __('Add New Stage', 'artpulse'),
            'new_item_name' => __('New Stage Name', 'artpulse'),
            'menu_name' => __('Project Stages', 'artpulse'),
        ];
        register_taxonomy('ap_project_stage', 'artpulse_artwork', [
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'project-stage'],
            'show_in_rest' => true,
        ]);
    }
}
