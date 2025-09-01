#!/usr/bin/env bash

set -euo pipefail

# Requires DB credentials via environment variables
: "${DB_NAME:?DB_NAME is not set}"
: "${DB_USER:?DB_USER is not set}"
: "${DB_PASSWORD:?DB_PASSWORD is not set}"
: "${DB_HOST:?DB_HOST is not set}"

WP_CORE_DIR=${WP_CORE_DIR:-/tmp/wordpress}
WP_TESTS_DIR=${WP_TESTS_DIR:-$(pwd)/vendor/wp-phpunit/wp-phpunit}

# Ensure Composer dependencies are installed
if [ ! -d "vendor" ]; then
  composer install >/dev/null
fi

if [ ! -f "$WP_CORE_DIR/wp-load.php" ]; then
  echo "Installing WordPress via Composer..."
  composer create-project --no-interaction --prefer-dist johnpbloch/wordpress "$WP_CORE_DIR" >/dev/null
fi

if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
  echo "Setting up WordPress test suite configuration..."
  cat >"$WP_TESTS_DIR/wp-tests-config.php" <<'EOF'
<?php
define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
$table_prefix = 'wptests_';
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'WP Tests' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '$WP_CORE_DIR/' );
}
EOF
fi

echo "Creating WordPress test database..."
mysqladmin drop "$DB_NAME" --user="$DB_USER" --password="$DB_PASSWORD" --host="$DB_HOST" --force >/dev/null 2>&1 || true
mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASSWORD" --host="$DB_HOST"

echo "WordPress test environment installed."

