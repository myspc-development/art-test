#!/usr/bin/env bash

# Usage: bin/install-wp-tests.sh <db-name> <db-user> <db-pass> <db-host> [wp-version]

set -e

if [ $# -lt 4 ]; then
  echo "Usage: $0 <db-name> <db-user> <db-pass> <db-host> [wp-version]"
  exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=$4
WP_VERSION=${5-latest}

WP_TESTS_DIR=/tmp/wordpress-tests-lib
WP_CORE_DIR=/tmp/wordpress

download() {
  if command -v curl >/dev/null; then
    curl -sSL "$1" -o "$2"
  elif command -v wget >/dev/null; then
    wget -q -O "$2" "$1"
  else
    echo "Error: neither curl nor wget is installed." >&2
    exit 1
  fi
}

install_wp() {
  if [ ! -d "$WP_CORE_DIR" ]; then
    mkdir -p "$WP_CORE_DIR"
  fi

  if [ ! -f "$WP_CORE_DIR/wp-load.php" ]; then
    echo "Downloading WordPress $WP_VERSION ..."
    local ARCHIVE="/tmp/wordpress.tar.gz"
    if [ "$WP_VERSION" = "latest" ]; then
      download https://wordpress.org/latest.tar.gz "$ARCHIVE"
    else
      download "https://wordpress.org/wordpress-$WP_VERSION.tar.gz" "$ARCHIVE"
    fi
    tar --strip-components=1 -zxmf "$ARCHIVE" -C "$WP_CORE_DIR"
    rm "$ARCHIVE"
  fi
}

install_test_suite() {
  if [ ! -d "$WP_TESTS_DIR" ]; then
    mkdir -p "$WP_TESTS_DIR"
  fi

  if [ ! -f "$WP_TESTS_DIR/includes/functions.php" ]; then
    echo "Installing WordPress test suite..."
    if ! command -v svn >/dev/null 2>&1; then
      echo "Error: svn is required to download the test suite." >&2
      exit 1
    fi

    if [ "$WP_VERSION" = "latest" ]; then
      svn export --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ "$WP_TESTS_DIR/includes"
      download https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
    else
      svn export --quiet https://develop.svn.wordpress.org/tags/"$WP_VERSION"/tests/phpunit/includes/ "$WP_TESTS_DIR/includes"
      download https://develop.svn.wordpress.org/tags/"$WP_VERSION"/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
    fi

    sed -i "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
    sed -i "s|localhost|$DB_HOST|" "$WP_TESTS_DIR/wp-tests-config.php"
  fi
}

install_db() {
  echo "Creating WordPress test database..."
  mysqladmin drop "$DB_NAME" --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST" --force >/dev/null 2>&1 || true
  mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST"
}

install_wp
install_test_suite
install_db

echo "WordPress test environment installed." 
