<?php
// Define the path to the PHPUnit Polyfills so WordPress can load them.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
    define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' );
}

// Load Composer's autoloader.
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Determine the WordPress tests directory.
$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = dirname( __DIR__, 2 ) . '/vendor/wp-phpunit/wp-phpunit';
}

// Give WordPress access to the plugin.
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function () {
    require dirname( __DIR__, 2 ) . '/artpulse.php';
} );

// Start up the WordPress testing environment.
require $_tests_dir . '/includes/bootstrap.php';
