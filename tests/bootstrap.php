<?php
// Prefer composer-installed WP tests
$tests_dir = getenv('WP_PHPUNIT__TESTS_DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
if ( ! is_dir( $tests_dir ) ) {
    fwrite(STDERR, "Could not find WordPress tests in {$tests_dir}\n");
    exit(1);
}

require $tests_dir . '/includes/functions.php';

// Load the plugin under test
// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- test bootstrap
require_once __DIR__ . '/Support/AjaxTestHelper.php';

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );
}

tests_add_filter( 'muplugins_loaded', function () {
    require dirname(__DIR__) . '/artpulse-management.php';
});

// Optional: verbose errors for debugging
ini_set( 'display_errors', '1' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );

require $tests_dir . '/includes/bootstrap.php';
