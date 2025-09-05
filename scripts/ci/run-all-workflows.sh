#!/usr/bin/env bash
set -euo pipefail

REF="${1:-$(git rev-parse --abbrev-ref HEAD)}"

echo "Triggering all dispatchable workflows at ref: $REF"
echo "Requires: gh CLI authenticated (gh auth login)"

# List of known workflows to trigger (edit as needed)
WORKFLOWS=(
  "ci.yml"            # main CI (PHPUnit + Jest)
  "run-all.yml"       # meta helper (no-op trigger)
  # add others here, e.g. "lint.yml", "build.yml"
)

for wf in "${WORKFLOWS[@]}"; do
  echo "→ gh workflow run $wf --ref $REF"
  gh workflow run "$wf" --ref "$REF" || true
  done

echo "Done. Check Actions tab for runs."
