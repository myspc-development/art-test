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

	// host:port or plain host
	if ( strpos( $raw, ':' ) !== false ) {
		[$h, $p] = explode( ':', $raw, 2 );
		$p       = ctype_digit( $p ) ? (int) $p : null;
		return array( trim( $h ), $p, null );
	}

	return array( $raw, null, null );
}

$errors = array();

// Ensure tests configuration exists by copying the sample if missing.
$root       = dirname( __DIR__ );
$configPath = $root . '/tests/wp-tests-config.php';
$samplePath = $root . '/tests/wp-tests-config-sample.php';
if ( ! file_exists( $configPath ) && file_exists( $samplePath ) ) {
    @copy( $samplePath, $configPath );
    echo 'Created tests/wp-tests-config.php from sample.' . "\n";
}

/** 1) mysqli extension */
if ( ! extension_loaded( 'mysqli' ) ) {
	$errors[] = 'The mysqli extension is not loaded (install/enable php-mysql for CLI).';
}

/** 2) Gather DB env (WP_TESTS_DB_* preferred, DB_* fallback) */
$dbHost = getenv( 'WP_TESTS_DB_HOST' ) ?: getenv( 'DB_HOST' ) ?: '127.0.0.1:3306';
$dbUser = getenv( 'WP_TESTS_DB_USER' ) ?: getenv( 'DB_USER' ) ?: '';
$dbPass = getenv( 'WP_TESTS_DB_PASSWORD' ) ?: getenv( 'DB_PASSWORD' ) ?: '';
$dbName = getenv( 'WP_TESTS_DB_NAME' ) ?: getenv( 'DB_NAME' ) ?: 'wordpress_test';

if ( $dbUser === '' ) {
	$errors[] = 'Database credentials not provided. Set WP_TESTS_DB_USER/PASSWORD/HOST/NAME.';
}

/** 3) DB connectivity (skip if mysqli missing to avoid fatal) */
if ( empty( $errors ) ) {
	[$host, $port, $socket] = parse_db_host( $dbHost );

	mysqli_report( MYSQLI_REPORT_OFF );
	$mysqli = mysqli_init();
	if ( ! $mysqli ) {
		$errors[] = 'Failed to init mysqli.';
	} else {
		// 5 second connect timeout
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
			// sanity ping
			if ( ! @mysqli_ping( $mysqli ) ) {
				$errors[] = 'DB ping failed after connect.';
			}
			@mysqli_close( $mysqli );
		}
	}
}

/** 4) WP test library */
$wpDir      = getenv( 'WP_PHPUNIT__DIR' ) ?: 'vendor/wp-phpunit/wp-phpunit';
$wpSettings = $wpDir . '/wordpress/wp-settings.php';
if ( ! file_exists( $wpSettings ) ) {
        $local = getenv( 'WP_CORE_DIR' );
        if ( ! $local ) {
                $errors[] = 'WP_CORE_DIR not set. Set WP_CORE_DIR to an existing WP root and run `composer run wp:core-link`.';
        } elseif ( ! file_exists( rtrim( $local, '/' ) . '/wp-settings.php' ) ) {
                $errors[] = 'wp-settings.php not found in WP_CORE_DIR=' . $local;
        } else {
                $targetDir = $wpDir . '/wordpress';
                if ( ! is_dir( dirname( $targetDir ) ) ) {
                        @mkdir( dirname( $targetDir ), 0777, true );
                }
                @unlink( $targetDir );
                @symlink( $local, $targetDir );
                if ( ! file_exists( $wpSettings ) ) {
                        $errors[] = 'wp core link failed: ' . $wpSettings . ' missing after linking';
                } else {
                        echo 'Linked ' . $local . ' -> ' . $targetDir . "\n";
                }
        }
}

/** 5) Optional: phpunit binary presence (warn only) */
$phpunitPaths = array(
	'vendor/bin/phpunit',
	'vendor/phpunit/phpunit/phpunit',
);
$hasPhpunit   = false;
foreach ( $phpunitPaths as $p ) {
	if ( is_file( $p ) || is_link( $p ) ) {
		$hasPhpunit = true;
		break; }
}
if ( ! $hasPhpunit ) {
	// Non-fatal, but helpful
	fwrite( STDERR, "Note: PHPUnit binary not found under vendor/. Run `composer install`.\n" );
}

/** 5) Coverage driver */
if ( ! extension_loaded( 'pcov' ) && ! extension_loaded( 'xdebug' ) ) {
	$errors[] = 'No code coverage driver available (install pcov or xdebug).';
}

/** 6) Outcome */
if ( $errors ) {
	fail( $errors );
}
echo "Preflight checks passed.\n";
