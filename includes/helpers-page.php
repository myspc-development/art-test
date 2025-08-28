<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create all required plugin pages if they do not exist.
 */
function artpulse_create_required_pages(): void {
	$pages = array(
		array( 'login', 'Login', '[ap_login]', 'page-login.php' ),
		array( 'dashboard', 'Dashboard', '', 'page-dashboard.php' ),
		array( 'events', 'Events', '', 'page-events.php' ),
		array( 'artists', 'Artists', '', 'page-artists.php' ),
	);

	foreach ( $pages as [$slug, $title, $content, $template] ) {
		artpulse_ensure_page( $slug, $title, $content, $template );
	}

	// Optional calendar page.
	artpulse_ensure_page( 'calendar', 'Calendar', '[ap_event_calendar]' );
}

/**
 * Ensure a page exists with the given parameters.
 */
function artpulse_ensure_page( string $slug, string $title, string $content = '', string $template = '' ): void {
	$page = get_page_by_path( $slug );
	if ( ! $page ) {
		$page_id = wp_insert_post(
			array(
				'post_name'    => $slug,
				'post_title'   => $title,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $content,
			)
		);
		if ( $template ) {
			update_post_meta( $page_id, '_wp_page_template', $template );
		}
	}
}

/**
 * Load plugin page templates by slug.
 */
function artpulse_template_loader( string $template ): string {
	$map = array(
		'login'     => 'page-login.php',
		'dashboard' => 'page-dashboard.php',
		'events'    => 'page-events.php',
		'artists'   => 'page-artists.php',
	);

	if ( is_page( array_keys( $map ) ) ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $map[ $slug ] ) ) {
			$file = plugin_dir_path( __DIR__ ) . 'templates/' . $map[ $slug ];
			if ( file_exists( $file ) ) {
				return $file;
			}
		}
	}

	return $template;
}
add_filter( 'template_include', 'artpulse_template_loader' );
