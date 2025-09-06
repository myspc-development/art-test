<?php
$plugin_root = dirname( __DIR__, 2 );
$script      = $plugin_root . '/scripts/widget-preview-guard-check.py';
$cmd         = 'python3 ' . escapeshellarg( $script );
$out         = array();
$return      = 0;
exec( $cmd, $out, $return );
foreach ( $out as $line ) {
	echo $line, "\n";
}
$report = $plugin_root . '/widget-preview-report.md';
$errors = array();
if ( file_exists( $report ) ) {
	$txt = file_get_contents( $report );
	if ( preg_match( '/Widgets unguarded: (\d+)/', $txt, $m ) && intval( $m[1] ) > 0 ) {
		$errors[] = 'Unguarded widgets detected';
	}
	if ( preg_match( '/## Unmapped Files\n(- .+\n)+/', $txt ) ) {
		$errors[] = 'Unmapped widget files detected';
	}
}
if ( $errors ) {
	foreach ( $errors as $e ) {
		echo "[FAIL] $e\n";
	}
	exit( 1 );
}

echo "Widget preview guard check passed\n";
