---
title: CI/CD Workflow Guide
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# CI/CD Workflow Guide

The repository includes a GitHub Actions workflow that installs dependencies, runs the test suite and packages the plugin for release. The workflow requires the following environment variables for the WordPress test environment:

- `DB_NAME` – name of the test database
- `DB_USER` – database user
- `DB_PASSWORD` – user password
- `DB_HOST` – database host (e.g. `127.0.0.1`)
- `DB_CHARSET` – optional character set (defaults to `utf8`)
- `DB_COLLATE` – optional collation

`WORDPRESS_TARBALL` can be set to a local WordPress archive if downloading is blocked.

Composer and npm dependencies are cached using the `actions/cache` action. The cache keys are derived from `composer.lock` and `package-lock.json` so caches are automatically invalidated when dependencies change.

After installing dependencies the workflow runs `npm run lint`, `npm test` and `composer sniff`. Assets are built with `npm run build` and the `scripts/release.sh` script creates a zip file in the `release/` directory. The archive is uploaded as a workflow artifact.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
