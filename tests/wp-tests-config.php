<?php
// WordPress test suite DB settings
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', '127.0.0.1' ); // force TCP, avoid socket auth issues
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// Table prefix for tests:
$table_prefix = 'wptests_';

// Helpful during test runs:
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
