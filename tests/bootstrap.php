<?php

// Define required WP test constants
if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
    define( 'WP_TESTS_DOMAIN', 'example.org' );
}
if ( ! defined( 'WP_TESTS_EMAIL' ) ) {
    define( 'WP_TESTS_EMAIL', 'admin@example.org' );
}
if ( ! defined( 'WP_TESTS_TITLE' ) ) {
    define( 'WP_TESTS_TITLE', 'Test Blog' );
}
if ( ! defined( 'WP_PHP_BINARY' ) ) {
    define( 'WP_PHP_BINARY', 'php' );
}
if ( ! defined( 'WP_TESTS_DIR' ) ) {
    define( 'WP_TESTS_DIR', getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib' );
}
if ( ! defined( 'WP_CORE_DIR' ) ) {
    define( 'WP_CORE_DIR', getenv( 'WP_CORE_DIR' ) ?: '/tmp/wordpress' );
}
// Ensure the core bootstrap can locate the configuration file.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', WP_TESTS_DIR . '/wp-tests-config.php' );
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
    require dirname(__DIR__) . '/artpulse-management.php';

    // Ensure core tables exist when running under PHPUnit since activation
    // hooks do not fire in this context.
    if (function_exists('ap_create_all_tables')) {
        ap_create_all_tables();
    }
    if (function_exists('ArtPulse\\DB\\create_monetization_tables')) {
        ArtPulse\DB\create_monetization_tables();
    }
});

require_once WP_TESTS_DIR . '/includes/bootstrap.php';

// Centralized mock
if (!function_exists('ArtPulse\\Admin\\current_user_can')) {
    eval('namespace ArtPulse\\Admin; function current_user_can($cap) { return true; }');
}
