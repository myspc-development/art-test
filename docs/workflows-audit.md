# CI Workflows Audit

This repository uses **npm** (via `package-lock.json`) and **Composer** for dependency management.

## Inventory

| Workflow | Triggers | Main Checks | Notes |
|---|---|---|---|
| `js-lint-typecheck` | push, pull_request, workflow_dispatch | ESLint, TypeScript compilation | Caches npm deps, uploads ESLint report |
| `jest-unit` | push, pull_request, workflow_dispatch | Jest unit tests w/ coverage | Matrix on Node LTS & current |
| `widgets-unit` | pull_request, workflow_dispatch | Focused Jest tests for widgets & dashboards | Lacks explicit console.error guard |
| `wp-integration-tests` | pull_request, schedule, workflow_dispatch | PHPUnit against WP matrix (6.3‑6.6, PHP 8.1‑8.3) | Generates REST endpoint summary |
| `rest-contract-check` | pull_request (REST paths, schema), workflow_dispatch | Fetch & validate `/wp-json/artpulse/v1/dashboard-config` with Ajv | uploads response payload |
| `e2e-login-dashboard` | pull_request, schedule, workflow_dispatch | Playwright login + role dashboards | uploads traces & junit |
| `dashboard-a11y-smoke` | — | ❌ Missing – recommended Axe smoke test |
| `widgets-visual-regression` | — | ❌ Missing – recommended Playwright snapshots |
| `dashboard-lighthouse` | — | ❌ Missing – recommended Lighthouse budgets |
| `bundlesize` | pull_request, workflow_dispatch | Size‑limit / bundlesize | reports bundle diff |
| `php-static-analysis` | push, pull_request, workflow_dispatch | PHPStan across PHP 8.1‑8.3 | uploads JSON report |
| `wp-coding-standards` | pull_request, workflow_dispatch | WordPress PHPCS | summary of top files |
| `build-package` | pull_request (main), tag push, workflow_dispatch | Build plugin ZIP, smoke install | uploads ZIP and status |
| `dependency-audit` | pull_request, schedule, workflow_dispatch | npm audit & composer audit | summary only |
| `codeql` | — | ❌ Missing – add JS + PHP CodeQL |
| `orchestrate-all` | schedule, workflow_dispatch | Dispatches all workflows & aggregates results | downloads artifacts |

## Hotspots & Gaps

* **Potential long runners:** `wp-integration-tests` (matrix over WP/PHP), `e2e-login-dashboard` (Playwright), `build-package` (WP env).
* **Missing summaries/artifacts:** most workflows already upload logs and summaries; `widgets-unit` could add explicit log upload and fail on `console.error`.
* **Missing jobs:** accessibility smoke, visual regression, Lighthouse, CodeQL.
* **Flakiness areas:** WP environment startup for integration/E2E jobs; REST contract may fail if endpoint unavailable.

## Recommendations

1. **Add missing workflows** for accessibility (`dashboard-a11y-smoke`), visual regression (`widgets-visual-regression`), performance (`dashboard-lighthouse`), and security (`codeql`). These provide critical UX, perf, and security signals.
2. **Harden widgets-unit** by failing on unexpected `console.error` and uploading artifacts for easier triage.
3. **Monitor long‑running jobs** (`wp-integration-tests`, `e2e-login-dashboard`). Consider splitting matrices or enabling retry-on-failure for known flaky steps (WP env start).
4. **Ensure npm/composer caching** is present across new workflows to reduce run time.
5. **Document local run helper.** Use `scripts/ci/run_all.sh` (added) to dispatch and watch workflows from the CLI.

## Running All Workflows Locally/CI

```bash
# Trigger all dispatchable workflows on current branch
bash scripts/ci/run_all.sh

# Or specify a ref
bash scripts/ci/run_all.sh feature/my-branch
```

The script uses the GitHub CLI (`gh`) to trigger each workflow and prints a table summarizing conclusions and links. Ensure `gh auth login` has been executed first.

> If a workflow listed above is missing in the repo, the script logs a warning and continues. Adding the recommended workflows will enable full coverage of login, role-aware dashboards, widgets, accessibility, performance, and security checks.

