#!/bin/bash
# Run this from your plugin root (/www/wwwroot/192.168.88.31/wp-content/plugins/artpulse-management-plugin-main)

set -e

echo "=== Downloading WordPress core into ./wordpress ==="
mkdir -p wordpress
curl -O https://wordpress.org/latest.tar.gz
tar -xzf latest.tar.gz -C wordpress --strip-components=1
rm latest.tar.gz

echo "=== Creating wp-tests-config.php with correct paths and DB settings ==="
cat > wp-tests-config.php << 'EOF'
<?php
// DB settings for your test database.
// Credentials can be provided via environment variables.
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

// Path to WordPress source
define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress' );

// Bootstrap the WordPress environment for testing
require_once ABSPATH . '/wp-settings.php';
EOF

echo "=== Done setting up wp-tests-config.php ==="
echo "You can now run: vendor/bin/phpunit --testdox"

