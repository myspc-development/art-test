<?php
/** Sample WordPress test configuration */

define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'wordpress_test' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASSWORD' ) ?: '' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1' );
define( 'WP_TESTS_DOMAIN', getenv( 'WP_TESTS_DOMAIN' ) ?: 'example.org' );
define( 'WP_TESTS_EMAIL', getenv( 'WP_TESTS_EMAIL' ) ?: 'admin@example.org' );
define( 'WP_TESTS_TITLE', getenv( 'WP_TESTS_TITLE' ) ?: 'Test Blog' );
define( 'WP_PHP_BINARY', getenv( 'WP_PHP_BINARY' ) ?: 'php' );

$table_prefix = 'wptests_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', false );
