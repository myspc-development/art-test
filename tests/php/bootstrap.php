<?php
/**
 * PHPUnit bootstrap for WordPress integration tests.
 */

$wp_phpunit_dir = getenv( 'WP_PHPUNIT__DIR' ) ?: __DIR__ . '/../../vendor/wp-phpunit/wp-phpunit';
$tests_config   = getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ?: __DIR__ . '/../wp-tests-config.php';

if ( ! file_exists( $wp_phpunit_dir . '/includes/bootstrap.php' ) ) {
	fwrite( STDERR, "\n[bootstrap] wp-phpunit not installed at {$wp_phpunit_dir}. Run composer install.\n" );
	exit( 1 );
}
if ( ! file_exists( $tests_config ) ) {
	fwrite( STDERR, "\n[bootstrap] Missing tests config at {$tests_config}.\n" );
	exit( 1 );
}

// Give WP test suite a chance to load first; plugin will load on muplugins_loaded.
require_once $wp_phpunit_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () {
		$plugin_main = getenv( 'PLUGIN_MAIN_FILE' ) ?: 'artpulse.php';
		$root        = realpath( __DIR__ . '/../../' );
		$candidate   = $root . '/' . ltrim( $plugin_main, '/' );

		if ( file_exists( $candidate ) ) {
			if ( ! defined( 'ARTPULSE_PLUGIN_FILE' ) ) {
				define( 'ARTPULSE_PLUGIN_FILE', $candidate );
			}
			if ( ! defined( 'ARTPULSE_PLUGIN_DIR' ) ) {
				define( 'ARTPULSE_PLUGIN_DIR', dirname( ARTPULSE_PLUGIN_FILE ) . '/' );
			}
			require_once $candidate;
		} else {
			// Fallback if plugin bootstrap is namespaced in src/Core/Plugin.php
			$alt = $root . '/src/Core/Plugin.php';
			if ( file_exists( $alt ) ) {
				require_once $alt;
			} else {
				fwrite( STDERR, "\n[bootstrap] Could not find plugin main file at: {$candidate}.\n" );
			}
		}
	}
);

// Now start WordPress (defines ABSPATH) and runs the test environment.
require $wp_phpunit_dir . '/includes/bootstrap.php';
require_once __DIR__ . '/../TestHelpers/filesystem.php';
