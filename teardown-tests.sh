#!/bin/bash

set -euo pipefail

# Requires DB credentials via environment variables
: "${DB_NAME:?DB_NAME is not set}"
: "${DB_USER:?DB_USER is not set}"
: "${DB_PASSWORD:?DB_PASSWORD is not set}"
: "${DB_HOST:?DB_HOST is not set}"

echo "Removing WordPress test installation..."
rm -rf wordpress tests/wp-tests-config.php

echo "Dropping test database..."
mysqladmin drop "$DB_NAME" --user="$DB_USER" --password="$DB_PASSWORD" --host="$DB_HOST" --force >/dev/null 2>&1 || true

echo "Teardown complete."

