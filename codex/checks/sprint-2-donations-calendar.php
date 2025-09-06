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

// Sprint 2: donations endpoint
$resp = wp_remote_post( rest_url( 'artpulse/v1/donations' ), array( 'body' => array() ) );
if ( is_wp_error( $resp ) ) {
	$errors[] = 'Donations endpoint failed';
}

// Sprint 2: calendar feed
$cal = wp_remote_get( rest_url( 'ap/v1/calendar' ) );
if ( is_wp_error( $cal ) ) {
	$errors[] = 'Calendar endpoint missing';
} else {
	$body = json_decode( wp_remote_retrieve_body( $cal ), true );
	if ( ! is_array( $body ) ) {
		$errors[] = 'Calendar response invalid';
	}
}

if ( $errors ) {
	foreach ( $errors as $e ) {
		echo "[FAIL] $e\n";
	}
	exit( 1 );
}

echo "Sprint 2 checks passed\n";
