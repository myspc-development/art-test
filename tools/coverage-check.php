<?php
if ( $argc < 4 ) {
	fwrite( STDERR, "Usage: coverage-check.php <coverage1.xml> [<coverage2.xml> ...] <overall> <Class=Threshold>...\n" );
	exit( 1 );
}

$files = array();
// Collect coverage files
while ( $argc > 1 && file_exists( $argv[1] ) ) {
	$files[] = array_shift( $argv );
	--$argc;
}

if ( ! $files ) {
	fwrite( STDERR, "No coverage files provided\n" );
	exit( 1 );
}

$overall    = (float) array_shift( $argv );
$thresholds = array();
foreach ( $argv as $arg ) {
	if ( strpos( $arg, '=' ) === false ) {
		continue;
	}
	[$name, $min]        = explode( '=', $arg, 2 );
	$thresholds[ $name ] = (float) $min;
}

$totalStatements = 0;
$totalCovered    = 0;
$classMetrics    = array();

foreach ( $files as $file ) {
	$xml              = new SimpleXMLElement( file_get_contents( $file ) );
	$metrics          = $xml->project->metrics;
	$totalStatements += (int) $metrics['statements'];
	$totalCovered    += (int) $metrics['coveredstatements'];

	foreach ( $xml->xpath( '//class' ) as $class ) {
		$name = (string) $class['name'];
		$m    = $class->metrics;
		$stat = (int) $m['statements'];
		$cov  = (int) $m['coveredstatements'];
		if ( ! isset( $classMetrics[ $name ] ) ) {
			$classMetrics[ $name ] = array(
				'statements' => 0,
				'covered'    => 0,
			);
		}
		$classMetrics[ $name ]['statements'] += $stat;
		$classMetrics[ $name ]['covered']    += $cov;
	}
}

$overallPct = $totalStatements ? ( $totalCovered / $totalStatements * 100 ) : 0;
if ( $overallPct < $overall ) {
	fwrite( STDERR, sprintf( "Overall coverage %.2f%% is below required %.2f%%\n", $overallPct, $overall ) );
	exit( 1 );
}

foreach ( $thresholds as $class => $min ) {
	if ( ! isset( $classMetrics[ $class ] ) ) {
		fwrite( STDERR, "Missing coverage for class $class\n" );
		exit( 1 );
	}
	$stat = $classMetrics[ $class ]['statements'];
	$cov  = $classMetrics[ $class ]['covered'];
	$pct  = $stat ? ( $cov / $stat * 100 ) : 0;
	if ( $pct < $min ) {
		fwrite( STDERR, sprintf( "Coverage for %s is %.2f%%, below required %.2f%%\n", $class, $pct, $min ) );
		exit( 1 );
	}
}
exit( 0 );
