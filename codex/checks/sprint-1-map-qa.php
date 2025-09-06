<?php
$plugin_root   = dirname( __DIR__, 2 );
$wp_candidates = array(
	$plugin_root . '/wordpress/wp-load.php',
	$plugin_root . '/wp-load.php',
	dirname( $plugin_root ) . '/wp-load.php',
	dirname( $plugin_root, 2 ) . '/wp-load.php',
	dirname( $plugin_root, 3 ) . '/wp-load.php',
);
$wp_loaded     = false;
foreach ( $wp_candidates as $candidate ) {
	if ( file_exists( $candidate ) ) {
		require_once $candidate;
		$wp_loaded = true;
		break;
	}
}
if ( ! $wp_loaded ) {
	fwrite( STDERR, "Could not locate wp-load.php\n" );
	exit( 1 );
}

$errors = array();

// Sprint 1: Check map filter
$response = wp_remote_get( rest_url( 'artpulse/v1/events?lat=40.7&lng=-74&within_km=5' ) );
if ( is_wp_error( $response ) ) {
	$errors[] = 'Map filter request failed: ' . $response->get_error_message();
} else {
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) ) {
		$errors[] = 'Map filter returned non-array.';
	}
}

// Sprint 1: Check if qa_thread CPT is registered
$post_type = get_post_type_object( 'qa_thread' );
if ( null === $post_type ) {
	$errors[] = 'qa_thread post type missing.';
}

// Sprint 1: /qa-thread/{event} route
$request = new WP_REST_Request( 'GET', '/artpulse/v1/qa-thread/1' );
$server  = rest_get_server();
$result  = $server->dispatch( $request );
if ( $result instanceof WP_Error ) {
	$errors[] = '/qa-thread/{event} route failed.';
}

// Sprint 1: ap_event_rankings table existence
global $wpdb;
$table = $wpdb->get_var( "SHOW TABLES LIKE 'ap_event_rankings'" );
if ( ! $table ) {
	$errors[] = 'ap_event_rankings table missing.';
}

// Sprint 1: API key bearer auth test
$api_request = new WP_REST_Request( 'GET', '/api/v1/events' );
$api_request->set_header( 'Authorization', 'Bearer test' );
$api_result = $server->dispatch( $api_request );
if ( $api_result instanceof WP_Error ) {
	$errors[] = 'Bearer auth check failed.';
}

// Sprint 1: Widget script check
$widget_response = wp_remote_get( home_url( '/assets/widget.js' ) );
if ( is_wp_error( $widget_response ) ) {
	$errors[] = 'Widget script request failed.';
} else {
	$code = wp_remote_retrieve_body( $widget_response );
	if ( false === strpos( $code, '<iframe' ) ) {
		$errors[] = 'Widget script missing iframe.';
	}
}

if ( $errors ) {
	foreach ( $errors as $e ) {
		echo "[FAIL] $e\n";
	}
	exit( 1 );
}

echo "Sprint 1 checks passed\n";
