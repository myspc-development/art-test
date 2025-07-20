---
title: Plugin Installation & Updates
category: admin
role: admin
last_updated: 2025-07-20
status: complete
---
# Plugin Installation & Updates


## Manual Installation

1. Download the latest release zip from the GitHub repository.
2. Unzip the file and upload the plugin folder to `wp-content/plugins`.
3. Log into WordPress and activate **ArtPulse Management Plugin** from the Plugins screen.
4. Navigate to **ArtPulse → Settings** to verify that the version number matches the downloaded archive.

### Versioning Tips

- Keep older versions in a `backup/` folder so you can roll back if needed.
- Database updates run during activation. If you roll back, restore the previous database backup as well.

## Installing via Plugin Manager

1. In the WordPress admin, go to **Plugins → Add New**.
2. Click **Upload Plugin** and choose the release zip file.
3. After the upload completes, click **Activate Plugin**.
4. The plugin will check for required tables and create them if missing.

## Updating the Plugin

Updates appear in the standard WordPress updates screen when the site can reach the GitHub release feed. You can also install a zip over the existing directory.

1. Visit **Dashboard → Updates** and check for **ArtPulse**.
2. Click **Update Now** or upload the new zip through the Plugin Manager.
3. After the update, clear any caches and confirm that widgets still load correctly.

### Rollback

If an update introduces issues you can revert to the previous release:

1. Deactivate the plugin.
2. Delete the plugin directory without removing data.
3. Upload the prior version from your backups and activate it again.
4. Restore your database backup if necessary.

The **Updates** tab under **ArtPulse → Settings** shows the currently installed version and provides a manual **Check for Updates** button.

## Automated Updates

Enabling automatic updates ensures administrators always receive the latest bug fixes and security patches without manual intervention. In the WordPress Plugins list, click **Enable Auto-updates** next to **ArtPulse Management**. Alternatively, network admins can define `auto_update_plugins` in `wp-config.php` to keep all active plugins current. The plugin exposes a version check endpoint that returns the latest tagged release. Sites with the GitHub Updater plugin installed can poll this endpoint and install new versions as soon as they are published. After enabling automatic updates monitor the changelog for breaking changes and confirm that scheduled tasks continue to run correctly.