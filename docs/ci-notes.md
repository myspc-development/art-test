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
