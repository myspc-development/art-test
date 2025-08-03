#!/bin/bash
set -e

# Verify that widget PHP files under widgets/ are listed in both manifest files
# and that all manifest entries reference existing files.

MANIFESTS=("widget-manifest.json" "widget-manifest-with-health.json")
WIDGET_DIR="widgets"
STATUS=0

# Collect widget PHP files
widget_files=$(find "$WIDGET_DIR" -maxdepth 1 -type f -name '*Widget*.php' | sort)

for file in $widget_files; do
  for manifest in "${MANIFESTS[@]}"; do
    if ! jq -e --arg f "$file" 'any(.[]; .file == $f)' "$manifest" > /dev/null; then
      echo "Missing $file in $manifest"
      STATUS=1
    fi
  done
done

# Ensure manifest entries reference existing files
for manifest in "${MANIFESTS[@]}"; do
  while IFS= read -r path; do
    if [ ! -e "$path" ]; then
      echo "$manifest references missing file $path"
      STATUS=1
    fi
  done < <(jq -r '.[].file' "$manifest")
done

if [ $STATUS -ne 0 ]; then
  echo "Widget manifest check failed"
  exit 1
else
  echo "Widget manifest check passed"
fi
