<?php
// Load Composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Include WordPress PHPUnit polyfills
if (!defined('WP_TESTS_PHPUNIT_POLYFILLS_PATH')) {
    define(
        'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
        dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php'
    );
}

// Load the WordPress test environment
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );
}
require dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';

// Include function stubs used by tests
require __DIR__ . '/TestHelpers/FunctionStubs.php';

