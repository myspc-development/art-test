#!/usr/bin/env bash
set -e

# Phase 8: Documentation & Release Packaging Script
# File: scripts/release.sh

PLUGIN_DIR="$(pwd)"
PLUGIN_NAME="artpulse-management-plugin"

# Extract version from plugin header
VERSION=$(grep -m1 '^ \* Version:' artpulse-management.php | awk '{print $2}')
RELEASE_DIR="$PLUGIN_DIR/release"
ZIP_FILE="$PLUGIN_NAME-$VERSION.zip"

echo "🔨 Building release package for version $VERSION …"

# 1. Prepare release directory
rm -rf "$RELEASE_DIR"
mkdir -p "$RELEASE_DIR"

# 2. Install production dependencies
composer install --no-dev --optimize-autoloader

# 3. Build assets
npm run build

# Ensure CSS bundle exists for deployment
if [ ! -f dist/bundle.css ]; then
  echo "❌ dist/bundle.css is missing. Run 'npm run build' to generate it." >&2
  exit 1
fi

# 4. Ensure optimized autoloader for packaging
composer install --no-dev --optimize-autoloader

# 5. Copy plugin files to temp directory
TMPDIR=$(mktemp -d)
echo "📂 Copying files to temp dir $TMPDIR"
rsync -a --exclude 'scripts' --exclude 'tests' --exclude '.git' --exclude 'phpunit.unit.xml*' --exclude 'phpunit.wp.xml*' "$PLUGIN_DIR/" "$TMPDIR/"
# 6. Create ZIP archive
cd "$TMPDIR"
zip -r "$RELEASE_DIR/$ZIP_FILE" .
cd -

echo "✅ Release package created: $RELEASE_DIR/$ZIP_FILE"

# 7. Cleanup
rm -rf "$TMPDIR"

echo "🎉 Release script complete!"
