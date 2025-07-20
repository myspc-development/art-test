---
title: Updates Codex
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Updates Codex

This reference describes how the ArtPulse plugin handles application and database updates. Use it to ensure upgrade procedures remain reliable across environments.

## Release Channels
Stable releases are tagged in the main GitHub repository. Beta builds are published to the `develop` branch for testing. Administrators can install either channel by downloading the associated zip or enabling the built-in updater. Documented features should reference the release in which they first appeared.

## Update Workflow
1. When a new version is available the admin notices banner appears on the WordPress dashboard.
2. Clicking **Update Now** triggers the plugin upgrader. It downloads the package, unpacks it to a temporary folder and performs a version check.
3. If database changes are required the `ap_run_migrations()` function executes migration scripts sequentially. Each migration updates the stored plugin version so it is not run twice.
4. After the update completes the plugin clears caches and flushes rewrite rules to ensure new endpoints function correctly.

## Manual Upgrades
Sites without internet access can perform manual upgrades. Download the release zip and upload it through the Plugin Manager. Be sure to deactivate and delete the old folder first to avoid leftover files. Database migrations will still run on activation.

## Cron-Based Checks
The plugin uses the `ap_check_updates` cron job to poll the release endpoint once per day. Administrators can force a check from **ArtPulse → Settings → Updates**. If the site is behind a firewall, whitelist the GitHub domain so the update check succeeds.

## Troubleshooting
If an upgrade fails, enable `WP_DEBUG` and inspect the PHP error log. Missing dependencies or outdated PHP versions are common culprits. You can roll back by restoring the previous plugin folder and database backup. Always verify that custom widgets still load correctly after an update.

Refer to this codex whenever you publish a new version or modify the migration system. Consistent update practices keep the plugin secure and minimize downtime for users.