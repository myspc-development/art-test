<?php

// Define required WP test constants
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_TESTS_DIR', dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit' );
// Ensure the core bootstrap can locate the configuration file.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __DIR__ ) . '/wp-tests-config.php' );
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Optional helpers
if (file_exists(__DIR__ . '/TestHelpers.php')) {
    require_once __DIR__ . '/TestHelpers.php';
}

if (!file_exists(WP_TESTS_DIR . '/includes/bootstrap.php')) {
    echo "Missing WordPress test library in: " . WP_TESTS_DIR . "\n";
    exit(1);
}

require_once WP_TESTS_DIR . '/includes/functions.php';

tests_add_filter('muplugins_loaded', static function () {
    require dirname(__DIR__) . '/artpulse-management.php'; // or your main plugin file
});

require_once WP_TESTS_DIR . '/includes/bootstrap.php';

// Centralized mock
if (!function_exists('ArtPulse\\Admin\\current_user_can')) {
    eval('namespace ArtPulse\\Admin; function current_user_can($cap) { return true; }');
}
