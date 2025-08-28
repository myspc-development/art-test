<?php
// Prefer composer-installed WP tests
$tests_dir = getenv('WP_PHPUNIT__TESTS_DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
if ( ! is_dir( $tests_dir ) ) {
    fwrite(STDERR, "Could not find WordPress tests in {$tests_dir}\n");
    exit(1);
}

// Try to locate a WordPress source directory if not provided.
if ( ! getenv( 'WP_PHPUNIT__WP_DIR' ) ) {
    $candidates = [ dirname( __DIR__ ) . '/wordpress', '/www/wwwroot/192.168.1.21' ];
    foreach ( $candidates as $dir ) {
        if ( is_dir( $dir ) ) {
            putenv( 'WP_PHPUNIT__WP_DIR=' . $dir );
            break;
        }
    }
}

require $tests_dir . '/includes/functions.php';

// Load the plugin under test
// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- test bootstrap
require_once __DIR__ . '/Support/AjaxTestHelper.php';

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
    define( 'WP_TESTS_CONFIG_FILE_PATH', __DIR__ . '/wp-tests-config.php' );
}

tests_add_filter( 'muplugins_loaded', function () {
    require dirname(__DIR__) . '/artpulse-management.php';

    // Ensure DB tables exist in test runs
    if ( function_exists( 'artpulse_activate' ) ) {
        artpulse_activate();
    }
    if ( function_exists( 'ap_install_tables' ) ) {
        ap_install_tables();
    } elseif ( function_exists( 'ap_install' ) ) {
        ap_install();
    } elseif ( function_exists( 'artpulse_install' ) ) {
        artpulse_install();
    } elseif ( class_exists( '\\ArtPulse\\Installer' ) && method_exists( '\\ArtPulse\\Installer', 'install' ) ) {
        \ArtPulse\Installer::install();
    } elseif ( file_exists( __DIR__ . '/../includes/db-schema.php' ) ) {
        require_once __DIR__ . '/../includes/db-schema.php';
        if ( function_exists( 'ap_install' ) ) { ap_install(); }
    }
    if ( class_exists( '\\ArtPulse\\Core\\FeedbackManager' ) && method_exists( '\\ArtPulse\\Core\\FeedbackManager', 'install_table' ) ) {
        \ArtPulse\Core\FeedbackManager::install_table();
    }
});

// Optional: verbose errors for debugging
ini_set( 'display_errors', '1' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );

if ( defined( 'AP_TEST_CLOSURE_GUARD' ) ) {
    $guard = static function ( $value ) {
        if ( $value instanceof \Closure ) {
            throw new \RuntimeException( 'Closure values are not allowed in tests.' );
        }
        return $value;
    };
    add_filter( 'pre_update_option', $guard );
    add_filter( 'pre_set_transient', $guard );
}

require $tests_dir . '/includes/bootstrap.php';
