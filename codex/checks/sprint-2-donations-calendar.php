<?php
require_once dirname(__DIR__, 2) . '/wp-load.php';

$errors = [];

// Sprint 2: donations endpoint
$resp = wp_remote_post( rest_url('artpulse/v1/donations'), [ 'body' => [] ] );
if ( is_wp_error( $resp ) ) {
    $errors[] = 'Donations endpoint failed';
}

// Sprint 2: calendar feed
$cal = wp_remote_get( rest_url('artpulse/v1/calendar') );
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
    exit(1);
}

echo "Sprint 2 checks passed\n";
