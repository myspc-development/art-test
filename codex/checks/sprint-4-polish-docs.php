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

// Sprint 4: docs exist
$docs = array(
	'docs/codex-sprint-1-map-qa.md',
	'docs/codex-sprint-2-donations-calendar.md',
	'docs/codex-sprint-3-ranking-api.md',
	'docs/codex-sprint-4-polish-docs.md',
);
foreach ( $docs as $doc ) {
	if ( ! file_exists( dirname( __DIR__, 2 ) . '/' . $doc ) ) {
		$errors[] = "$doc missing";
	}
}

if ( $errors ) {
	foreach ( $errors as $e ) {
		echo "[FAIL] $e\n";
	}
	exit( 1 );
}

echo "Sprint 4 checks passed\n";
