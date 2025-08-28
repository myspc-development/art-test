#!/usr/bin/env php
<?php
mysqli_report( MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

$dbName     = getenv( 'WP_TESTS_DB_NAME' ) ?: getenv( 'DB_NAME' ) ?: 'wordpress_test';
$dbUser     = getenv( 'WP_TESTS_DB_USER' ) ?: getenv( 'DB_USER' ) ?: 'wp';
$dbPassword = getenv( 'WP_TESTS_DB_PASSWORD' ) ?: getenv( 'DB_PASSWORD' ) ?: 'password';
$dbHost     = getenv( 'WP_TESTS_DB_HOST' ) ?: getenv( 'DB_HOST' ) ?: 'localhost';

// Extract optional port or socket from host value.
$host   = $dbHost;
$port   = null;
$socket = null;
if ( strpos( $dbHost, ':' ) !== false ) {
	list($hostPart, $extra) = explode( ':', $dbHost, 2 );
	$host                   = $hostPart;
	if ( $extra !== '' ) {
		if ( ctype_digit( $extra ) ) {
			$port = (int) $extra;
		} else {
			$socket = $extra;
		}
	}
}

try {
	$mysqli        = new mysqli( $host, $dbUser, $dbPassword, '', $port ?? 0, $socket );
	$dbNameEscaped = $mysqli->real_escape_string( $dbName );
	$mysqli->query( "CREATE DATABASE IF NOT EXISTS `$dbNameEscaped` DEFAULT CHARACTER SET utf8mb4" );
	$mysqli->close();
	echo "Created database '$dbName' if not exists." . PHP_EOL;
} catch ( mysqli_sql_exception $e ) {
	fwrite( STDERR, 'Database creation failed: ' . $e->getMessage() . PHP_EOL );
	exit( 1 );
}
