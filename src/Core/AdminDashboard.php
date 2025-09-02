<?php
namespace ArtPulse\Core;

class AdminDashboard {

	public static function register() {
		add_action( 'admin_menu', array( self::class, 'addMenus' ) );
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue' ) );
	}

	public static function addMenus() {
		add_menu_page(
			__( 'ArtPulse', 'artpulse' ),
			__( 'ArtPulse', 'artpulse' ),
			'manage_options',
			'artpulse-dashboard',
			array( self::class, 'renderDashboard' ),
			'dashicons-art', // choose an appropriate dashicon
			60
		);
		add_submenu_page(
			'artpulse-dashboard',
			__( 'Events', 'artpulse' ),
			__( 'Events', 'artpulse' ),
			'edit_artpulse_events',
			'edit.php?post_type=artpulse_event'
		);
		add_submenu_page(
			'artpulse-dashboard',
			__( 'Artists', 'artpulse' ),
			__( 'Artists', 'artpulse' ),
			'edit_artpulse_artists',
			'edit.php?post_type=artpulse_artist'
		);
		add_submenu_page(
			'artpulse-dashboard',
			__( 'Artworks', 'artpulse' ),
			__( 'Artworks', 'artpulse' ),
			'edit_artpulse_artworks',
			'edit.php?post_type=artpulse_artwork'
		);
		add_submenu_page(
			'artpulse-dashboard',
			__( 'Organizations', 'artpulse' ),
			__( 'Organizations', 'artpulse' ),
			'edit_artpulse_orgs',
			'edit.php?post_type=artpulse_org'
		);
	}

	public static function enqueue( string $hook ): void {
		if ( $hook !== 'toplevel_page_artpulse-dashboard' ) {
			return;
		}

		// Legacy dashboard script removed.

               // Enable drag & drop and widget toggles within the dashboard preview
               $sortable_rel = 'assets/libs/sortablejs/Sortable.min.js';
               $role_rel     = 'assets/js/role-dashboard.js';

               $sortable_path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $sortable_rel;
               $sortable_ver  = file_exists( $sortable_path ) ? filemtime( $sortable_path ) : false;
               wp_enqueue_script(
                       'sortablejs',
                       plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . $sortable_rel,
                       array(),
                       $sortable_ver,
                       true
               );

               $role_path = plugin_dir_path( ARTPULSE_PLUGIN_FILE ) . $role_rel;
               $role_ver  = file_exists( $role_path ) ? filemtime( $role_path ) : false;
               wp_enqueue_script(
                       'role-dashboard',
                       plugin_dir_url( ARTPULSE_PLUGIN_FILE ) . $role_rel,
                       array( 'jquery', 'sortablejs' ),
                       $role_ver,
                       true
               );

		wp_localize_script(
			'role-dashboard',
			'ArtPulseDashboard',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ap_dashboard_nonce' ),
			)
		);
	}

	public static function renderDashboard() {
		\ArtPulse\Admin\DashboardWidgetTools::render();
	}
}
