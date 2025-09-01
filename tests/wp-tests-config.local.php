<?php
// DB settings for the *test* database (separate from production).
define( 'DB_NAME', getenv( 'WP_TESTS_DB_NAME' ) ?: 'wordpress_test' );
define( 'DB_USER', getenv( 'WP_TESTS_DB_USER' ) ?: 'root' );
define( 'DB_PASSWORD', getenv( 'WP_TESTS_DB_PASS' ) ?: '' );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ?: '127.0.0.1' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
$table_prefix = 'wptests_';
define( 'WP_DEBUG', true );

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );

// IMPORTANT: point ABSPATH to the *local* WordPress core directory, with trailing slash.
// Example for your server layout:
define( 'ABSPATH', '/www/wwwroot/192.168.1.21/' );
