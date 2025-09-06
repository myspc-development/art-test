<?php
$root   = dirname( __DIR__, 2 );
$errors = array();
foreach ( glob( $root . '/includes/migrations/*.php' ) as $file ) {
	$base  = basename( $file, '.php' );
	$tests = glob( $root . '/tests/Migrations/*' . $base . '*Test.php' );
	if ( ! $tests ) {
		$errors[] = 'Missing migration test for ' . $base;
		continue;
	}
	$hasIdempotent = false;
	foreach ( $tests as $test ) {
		$contents = file_get_contents( $test );
		if ( preg_match( '/function\s+test.*idempotent/i', $contents ) ) {
			$hasIdempotent = true;
			break;
		}
	}
	if ( ! $hasIdempotent ) {
		$errors[] = 'Migration test for ' . $base . ' lacks idempotent check';
	}
}
if ( $errors ) {
	foreach ( $errors as $e ) {
		echo "[FAIL] $e\n";
	}
	exit( 1 );
}
echo "Migration contract check passed\n";
