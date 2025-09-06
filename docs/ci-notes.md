# CI Notes

This repository uses **npm** as the package manager (detected via `package-lock.json`).

## Orchestrator

The `orchestrate-all` workflow dispatches the other diagnostic workflows using the GitHub CLI.
To run it locally:

```bash
gh workflow run orchestrate-all.yml --ref <branch>
```

It summarizes each workflow conclusion in the step summary and downloads artifacts into `artifacts/`.

## Summaries & Artifacts

Each workflow writes a markdown section to `$GITHUB_STEP_SUMMARY` and uploads logs, coverage and JUnit reports as artifacts.

## Local Reproduction

```bash
composer install
npm ci
npm test
```

WordPress integration tests rely on `wp-env` and Docker; run `npm run test:php:local` to reproduce.

## Local build & debug

Bundle the role dashboard and widget scripts with [esbuild](https://esbuild.github.io/):

```bash
npm run build  # one-off build to assets/dist/
npm run dev    # watch mode; rebuilds on save
```

The WordPress plugin enqueues files from `assets/dist/`, so run one of the above commands before loading dashboards or widgets locally.

## Workflow Commands

### Lint and Typecheck
Locally:

```bash
npm run lint:js
npm run typecheck
```

GitHub: `js-lint-typecheck.yml` workflow.

### Unit Tests

```bash
npm test:js            # all unit tests
npm test:js -- widgets # widgets/dashboard subset via widgets-unit.yml
```

### REST Contract Check

`node scripts/rest-contract-check.js` fetches `/wp-json/artpulse/v1/dashboard-config` and validates it with `schema/dashboard-config.schema.json`.

### E2E Login & Dashboards

Playwright tests live in `tests/e2e`.

```bash
npx playwright test tests/e2e/login-dashboard.spec.ts
```

`e2e-login-dashboard.yml` runs the same in CI and uploads traces & screenshots to the run artifacts.

## Test Credentials

Defaults from `.env.e2e.example`:

| Role           | User          | Pass      |
|----------------|---------------|-----------|
| Administrator  | `admin`       | `password`|
| Member         | `member`      | `password`|
| Artist         | `artist`      | `password`|
| Organization   | `organization`| `password`|

## Artifacts

* Jest coverage: `coverage/`
* Jest JUnit: `build/junit-jest.xml`
* Playwright traces/screenshots: `build/e2e/artifacts/`

## Assumptions & Tweaks

* Node 20+ provides `AbortController` and `TextEncoder` so tests polyfill only when missing.
* WordPress tests expect `WP_ADMIN_USER`/`WP_ADMIN_PASS` to be seeded or provided.
* REST contract check requires the dev server to expose `/wp-json/artpulse/v1/dashboard-config`.
