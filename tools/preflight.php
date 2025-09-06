<?php
declare(strict_types=1);

/**
 * Hermetic preflight for WP integration tests.
 * - Verifies mysqli extension, DB connectivity, and WP_PHPUNIT__DIR
 * - Supports DB_HOST as host, host:port, [ipv6]:port, or a unix socket path
 * - Exits non-zero with actionable errors
 */
function fail( array $errors ): void {
		fwrite( STDERR, "Preflight checks failed:\n - " . implode( "\n - ", $errors ) . "\n" );
		exit( 1 );
}

$errors = array();

$root      = dirname( __DIR__ );
$phpunit   = $root . '/vendor/bin/phpunit';
$wpSetting = $root . '/vendor/wp-phpunit/wp-phpunit/wordpress/wp-settings.php';
if ( ! file_exists( $phpunit ) || ! file_exists( $wpSetting ) ) {
		fwrite( STDERR, "Run: WP_CORE_DIR=/absolute/path php bin/wp-core-link\n" );
		exit( 1 );
}

/** Parse DB_HOST into [host, port, socket] */
function parse_db_host( string $raw ): array {
		$raw = trim( $raw );

		// Unix socket path (starts with /)
	if ( $raw !== '' && $raw[0] === '/' ) {
			return array( null, null, $raw );
	}

		// IPv6 with optional port: [::1]:3306
	if ( str_starts_with( $raw, '[' ) ) {
			$rbr = strpos( $raw, ']' );
		if ( $rbr !== false ) {
				$host = substr( $raw, 1, $rbr - 1 );
				$port = null;
			if ( isset( $raw[ $rbr + 1 ] ) && $raw[ $rbr + 1 ] === ':' ) {
				$port = (int) substr( $raw, $rbr + 2 );
			}
				return array( $host, $port ?: null, null );
		}
	}

		// host:port or host
		$host  = $raw;
		$port  = null;
		$colon = strrpos( $raw, ':' );
	if ( $colon !== false ) {
			$host = substr( $raw, 0, $colon );
			$port = (int) substr( $raw, $colon + 1 );
	}

		return array( $host ?: null, $port ?: null, null );
}

$configPath = $root . '/tests/wp-tests-config.php';
$samplePath = $root . '/tests/wp-tests-config-sample.php';

$requiredEnv = array( 'WP_TESTS_DB_NAME', 'WP_TESTS_DB_USER', 'WP_TESTS_DB_PASSWORD', 'WP_TESTS_DB_HOST' );
$missing     = array();
$envValues   = array();
foreach ( $requiredEnv as $env ) {
		$val = getenv( $env );
	if ( $val === false || $val === '' ) {
			$missing[] = $env;
	} else {
			$envValues[ $env ] = $val;
	}
}
if ( $missing ) {
		$examples = array(
			'WP_TESTS_DB_NAME'     => 'wordpress_test',
			'WP_TESTS_DB_USER'     => 'wordpress_test',
			'WP_TESTS_DB_PASSWORD' => '0preggers2',
			'WP_TESTS_DB_HOST'     => '127.0.0.1',
		);
		foreach ( $examples as $env => $example ) {
				fwrite( STDERR, "export {$env}={$example}\n" );
		}
		exit( 1 );
}

// Assign validated environment variables for use below.
$dbName = $envValues['WP_TESTS_DB_NAME'];
$dbUser = $envValues['WP_TESTS_DB_USER'];
$dbPass = $envValues['WP_TESTS_DB_PASSWORD'];
$dbHost = $envValues['WP_TESTS_DB_HOST'];

if ( ! file_exists( $configPath ) && file_exists( $samplePath ) ) {
	if ( ! @copy( $samplePath, $configPath ) ) {
			$errors[] = 'Failed to copy tests/wp-tests-config-sample.php to tests/wp-tests-config.php.';
	}
}

if ( ! file_exists( $configPath ) ) {
		$errors[] = 'tests/wp-tests-config.php missing (copy tests/wp-tests-config-sample.php).';
} else {
		$cfg      = file_get_contents( $configPath );
		$required = array( 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' );
	foreach ( $required as $const ) {
		if ( ! preg_match( '/define\(\s*["\']' . $const . '["\']/', $cfg ) ) {
				$errors[] = 'tests/wp-tests-config.php missing definition for ' . $const . '.';
		}
	}
}

if ( ! extension_loaded( 'mysqli' ) ) {
		$errors[] = 'The mysqli extension is not loaded (install/enable php-mysql for CLI).';
}



if ( empty( $errors ) ) {
		[$host, $port, $socket] = parse_db_host( $dbHost );

		mysqli_report( MYSQLI_REPORT_OFF );
		$mysqli = mysqli_init();
	if ( ! $mysqli ) {
			$errors[] = 'Failed to init mysqli.';
	} else {
			@mysqli_options( $mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 5 );

			$ok = false;
		if ( $socket ) {
				$ok = @mysqli_real_connect( $mysqli, null, $dbUser, $dbPass, $dbName, 0, $socket );
		} elseif ( $port ) {
				$ok = @mysqli_real_connect( $mysqli, $host, $dbUser, $dbPass, $dbName, $port );
		} else {
				$ok = @mysqli_real_connect( $mysqli, $host ?? '127.0.0.1', $dbUser, $dbPass, $dbName );
		}

		if ( ! $ok ) {
				$errno    = mysqli_connect_errno();
				$err      = mysqli_connect_error();
				$where    = $socket ? "socket={$socket}" : 'host=' . ( $host ?? '127.0.0.1' ) . ( $port ? ":{$port}" : '' );
				$errors[] = "DB connect failed ({$where} db={$dbName}): [{$errno}] {$err}";
		} else {
			if ( ! @mysqli_ping( $mysqli ) ) {
					$errors[] = 'DB ping failed after connect.';
			}
				@mysqli_close( $mysqli );
		}
	}
}

if ( ! extension_loaded( 'pcov' ) && ! extension_loaded( 'xdebug' ) ) {
		$errors[] = 'No code coverage driver available (install pcov or xdebug).';
}

if ( $errors ) {
		fail( $errors );
}
echo "✅ Preflight checks passed.\n";
