#!/bin/bash
# Run this from your plugin root (/www/wwwroot/192.168.88.31/wp-content/plugins/artpulse-management-plugin-main)

set -e

# Require DB connection details via environment variables
: "${DB_NAME:?DB_NAME is not set}"
: "${DB_USER:?DB_USER is not set}"
: "${DB_PASSWORD:?DB_PASSWORD is not set}"
: "${DB_HOST:?DB_HOST is not set}"

if [ ! -f wordpress/wp-load.php ]; then
    echo "=== Installing WordPress via Composer into ./wordpress ==="
    composer create-project --no-interaction --prefer-dist johnpbloch/wordpress wordpress >/dev/null
else
    echo "WordPress already installed in ./wordpress"
fi

echo "=== Creating wp-tests-config.php with environment-based DB settings ==="
cat > wp-tests-config.php <<'EOF'
<?php
// DB settings for your test database.
define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );
define( 'DB_CHARSET', getenv( 'DB_CHARSET' ) ?: 'utf8' );
define( 'DB_COLLATE', getenv( 'DB_COLLATE' ) ?: '' );

// Site constants required by WP test suite
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

// PHP binary for running tests
define( 'WP_PHP_BINARY', 'php' );

// Path to WordPress source
define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress/' );
EOF

echo "=== Done setting up wp-tests-config.php ==="
echo "You can now run: vendor/bin/phpunit --testdox"

