<?php
require_once dirname(__DIR__, 2) . '/wp-load.php';

$errors = [];

// Sprint 3: rankings table check
global $wpdb;
$table = $wpdb->get_var("SHOW TABLES LIKE 'ap_event_rankings'");
if ( ! $table ) {
    $errors[] = 'ap_event_rankings table missing';
}

// Sprint 3: ranking endpoint
$resp = wp_remote_get( rest_url('api/v1/events?orderby=rank') );
if ( is_wp_error( $resp ) ) {
    $errors[] = '/api/v1/events rank endpoint failed';
}

// Sprint 3: API key middleware check
$req = new WP_REST_Request('GET', '/api/v1/events');
$req->set_header('Authorization', 'Bearer test');
$server = rest_get_server();
$result = $server->dispatch( $req );
if ( $result instanceof WP_Error ) {
    $errors[] = 'Partner API bearer auth failed';
}

if ( $errors ) {
    foreach ( $errors as $e ) {
        echo "[FAIL] $e\n";
    }
    exit(1);
}

echo "Sprint 3 checks passed\n";
