<?php
// DB creds (yours)
define( 'DB_NAME',     getenv('DB_NAME') ?: 'wordpress_test' );
define( 'DB_USER',     getenv('DB_USER') ?: 'wordpress_test' );
define( 'DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '0preggers2' );
define( 'DB_HOST',     getenv('DB_HOST') ?: '127.0.0.1' );

// Standard WP tests settings
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
$table_prefix = 'wptests_';
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'WP Tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );

// 👇 IMPORTANT: tell the installer where to put WordPress core for tests
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/wordpress/' );
}
