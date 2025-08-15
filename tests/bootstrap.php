<?php
/**
 * PHPUnit bootstrap for ArtPulse.
 *
 * @package ArtPulse
 */

// Load WP test suite.
$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
}
require $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () {
			// Load the plugin under test.
			require dirname( __DIR__ ) . '/artpulse.php';
	}
);

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );
}
require $_tests_dir . '/includes/bootstrap.php';

// Simple helpers.
/**
 * Instantiate a class via the container if available.
 *
 * @param string $class_name Class name to instantiate.
 * @return object Instance of the class.
 */
function ap_make( $class_name ) {
		return \function_exists( 'ap' ) ? ap()->make( $class_name ) : new $class_name();
}
