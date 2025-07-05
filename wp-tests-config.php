<?php
/**
 * WordPress test suite configuration file.
 */

// DB settings for your test database.
// Credentials can be provided as environment variables or defined elsewhere.
if ( ! defined( 'DB_NAME' ) ) {
    define( 'DB_NAME', getenv( 'DB_NAME' ) ?: 'sql_192_168_88_3' );
}

if ( ! defined( 'DB_USER' ) ) {
    define( 'DB_USER', getenv( 'DB_USER' ) ?: 'sql_192_168_88_3' );
}

if ( ! defined( 'DB_PASSWORD' ) ) {
    define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) ?: 'beca2446d15b1' );
}

if ( ! defined( 'DB_HOST' ) ) {
    define( 'DB_HOST', getenv( 'DB_HOST' ) ?: '127.0.0.1' );
}

if ( ! defined( 'DB_CHARSET' ) ) {
    define( 'DB_CHARSET', getenv( 'DB_CHARSET' ) ?: 'utf8mb4' );
}

if ( ! defined( 'DB_COLLATE' ) ) {
    define( 'DB_COLLATE', getenv( 'DB_COLLATE' ) ?: '' );
}

// Site constants required by WP test suite
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

// PHP binary for running tests
define( 'WP_PHP_BINARY', 'php' );

// Path to WordPress source; adjust if you use a different setup
define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress' );

// Bootstrap the WordPress environment for testing
require_once ABSPATH . '/wp-settings.php';
