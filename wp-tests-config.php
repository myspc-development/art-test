<?php
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' ); // Adjust if using PHP 8.1+

define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
$table_prefix = 'wptests_';

// Required for some wp-phpunit setups
define( 'WP_TESTS_DIR', __DIR__ . '/vendor/wp-phpunit/wp-phpunit' );
// Path to the WordPress installation used by the test suite.
if ( getenv( 'WP_CORE_DIR' ) ) {
    define( 'ABSPATH', rtrim( getenv( 'WP_CORE_DIR' ), '/' ) . '/' );
} else {
    define( 'ABSPATH', __DIR__ . '/wordpress/' );
}
