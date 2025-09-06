<?php
// tools/print-failures.php
$xml = @simplexml_load_file( $argv[1] ?? 'build/junit.xml' );
if ( ! $xml ) {
	fwrite( STDERR, "No junit.xml\n" );
	exit( 1 ); }
$fail = 0;
foreach ( $xml->xpath( '//testcase[failure|error]' ) as $tc ) {
	$cls  = (string) $tc['classname'];
	$name = (string) $tc['name'];
	$msg  = trim( (string) ( $tc->failure ?: $tc->error ) );
	echo "\n⛔  {$cls}::{$name}\n";
	if ( preg_match( '/#0\s+([^\n]+):(\d+)/', $msg, $m ) ) {
		echo "   at: {$m[1]}:{$m[2]}\n";
	}
	echo '   msg: ' . preg_replace( '/\s+/', ' ', $msg ) . "\n";
	++$fail;
}
echo "\nTotal failing tests: {$fail}\n";
