#!/usr/bin/env bash
set -euo pipefail

REF="${1:-$(git rev-parse --abbrev-ref HEAD)}"
REMOTE_URL=$(git remote get-url origin 2>/dev/null || true)
REPO="${GITHUB_REPOSITORY:-$(echo "$REMOTE_URL" | sed -E 's#(.*github.com[:/])([^/]+/[^/.]+)(\\.git)?#\2#') }"
REPO=${REPO:-unknown/unknown}

WORKFLOWS=(
  "js-lint-typecheck"
  "jest-unit"
  "widgets-unit"
  "wp-integration-tests"
  "rest-contract-check"
  "e2e-login-dashboard"
  "dashboard-a11y-smoke"
  "widgets-visual-regression"
  "dashboard-lighthouse"
  "bundlesize"
  "php-static-analysis"
  "wp-coding-standards"
  "codeql"
  "build-package"
)

echo "Triggering workflows on ref: $REF" >&2
printf '| Workflow | Conclusion | URL |\n' > table.md
printf '|---|---|---|\n' >> table.md

for wf in "${WORKFLOWS[@]}"; do
  if ! gh workflow list --json name 2>/dev/null | jq -er '.[] | select(.name=="'"$wf"'")' >/dev/null; then
    echo "workflow $wf not found, skipping" >&2
    continue
  fi
  gh workflow run "$wf" --ref "$REF" >/dev/null || true
  run_id=""
  for i in {1..30}; do
    run_id=$(gh run list --workflow "$wf" --branch "$REF" --limit 1 --json databaseId -q '.[0].databaseId' 2>/dev/null || true)
    [[ -n "$run_id" ]] && break
    sleep 2
  done
  if [[ -z "$run_id" ]]; then
    echo "could not determine run id for $wf" >&2
    continue
  fi
  gh run watch "$run_id" || true
  concl=$(gh run view "$run_id" --json conclusion -q .conclusion)
  url="https://github.com/$REPO/actions/runs/$run_id"
  printf '| %s | %s | [logs](%s) |\n' "$wf" "$concl" "$url" >> table.md
  gh run download "$run_id" -D "artifacts/$wf" >/dev/null 2>&1 || true
  echo "$wf done" >&2
done

cat table.md
