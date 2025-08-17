<?php
/**
 * WordPress PHPUnit configuration.
 * Reads DB settings from environment with sensible defaults.
 */
define( 'DB_NAME',     getenv( 'WP_DB_NAME' )     ?: 'wordpress_test' );
define( 'DB_USER',     getenv( 'WP_DB_USER' )     ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_DB_PASS' )     ?: '' );
define( 'DB_HOST',     getenv( 'WP_DB_HOST' )     ?: '127.0.0.1:3306' );
define( 'DB_CHARSET',  getenv( 'WP_DB_CHARSET' )  ?: 'utf8' );
define( 'DB_COLLATE',  getenv( 'WP_DB_COLLATE' )  ?: '' );
$table_prefix = getenv( 'WP_TABLE_PREFIX' ) ?: 'wptests_';
define( 'ABSPATH', dirname( __FILE__, 3 ) . '/wordpress/' );
define( 'WP_DEBUG', true );
define( 'WP_HTTP_BLOCK_EXTERNAL', true );
define( 'WP_ACCESSIBLE_HOSTS', '' );
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
