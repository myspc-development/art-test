<?php
/**
 * WordPress test suite configuration file.
 */

// Allow environment variables or an existing test suite to set these constants.
if ( ! defined( 'DB_NAME' ) ) {
    define( 'DB_NAME', getenv( 'DB_NAME' ) );
}
if ( ! defined( 'DB_USER' ) ) {
    define( 'DB_USER', getenv( 'DB_USER' ) );
}
if ( ! defined( 'DB_PASSWORD' ) ) {
    define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
}
if ( ! defined( 'DB_HOST' ) ) {
    define( 'DB_HOST', getenv( 'DB_HOST' ) );
}
if ( ! defined( 'DB_CHARSET' ) ) {
    define( 'DB_CHARSET', getenv( 'DB_CHARSET' ) );
}
if ( ! defined( 'DB_COLLATE' ) ) {
    define( 'DB_COLLATE', getenv( 'DB_COLLATE' ) );
}

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

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress/' );
}

// Prefix for tables created during tests.
if ( ! isset( $table_prefix ) ) {
    $table_prefix = 'wptests_';
}

require_once ABSPATH . '/wp-settings.php';
