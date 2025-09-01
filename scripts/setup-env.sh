#!/usr/bin/env bash
set -e

WORDPRESS_DIR="wordpress"

# Optional path to a pre-downloaded WordPress archive
WORDPRESS_TARBALL="${WORDPRESS_TARBALL:-}" 

mkdir -p "$WORDPRESS_DIR"
if [ ! -f "$WORDPRESS_DIR/wp-load.php" ]; then
    if [ -n "$WORDPRESS_TARBALL" ] && [ -f "$WORDPRESS_TARBALL" ]; then
        echo "Using WordPress archive $WORDPRESS_TARBALL"
        tar -xzf "$WORDPRESS_TARBALL" -C "$WORDPRESS_DIR" --strip-components=1
    else
        echo "Downloading WordPress..."
        curl -L https://wordpress.org/latest.tar.gz -o /tmp/wordpress.tar.gz
        tar -xzf /tmp/wordpress.tar.gz -C "$WORDPRESS_DIR" --strip-components=1
        rm /tmp/wordpress.tar.gz
    fi
else
    echo "WordPress already present in $WORDPRESS_DIR"
fi

if [ -n "$CI" ]; then
    echo "CI detected: installing Composer dependencies..."
    composer install --prefer-dist --no-interaction --no-progress
else
    echo "Installing Composer dependencies..."
    composer install
fi

echo "Installing Node dependencies..."
npm install

mkdir -p tests
cat > tests/wp-tests-config.php <<'PHP'
<?php
/**
 * WordPress test suite configuration file.
 */

define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );
define( 'DB_CHARSET', getenv( 'DB_CHARSET' ) );
define( 'DB_COLLATE', getenv( 'DB_COLLATE' ) );

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

define( 'ABSPATH', dirname( __FILE__ ) . '/../wordpress/' );

require_once ABSPATH . '/wp-settings.php';
PHP

echo "Environment setup complete."
