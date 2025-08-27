#!/usr/bin/env bash
set -euo pipefail

CORE_TARBALL=".ci/wp-phpunit-core.tar.zst"
TARGET_DIR="vendor/wp-phpunit/wp-phpunit"

if [ -f "$TARGET_DIR/wp-settings.php" ]; then
  echo "wp-phpunit core already present at $TARGET_DIR" >&2
  exit 0
fi

if [ ! -f "$CORE_TARBALL" ]; then
  echo "Missing artifact $CORE_TARBALL; cannot provision wp-phpunit core." >&2
  exit 1
fi

mkdir -p "$(dirname "$TARGET_DIR")"

tar --zstd -xf "$CORE_TARBALL" -C vendor/wp-phpunit

echo "wp-phpunit core unpacked to $TARGET_DIR" >&2
