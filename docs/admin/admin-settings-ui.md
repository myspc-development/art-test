---
title: Admin Settings UI
category: admin
role: developer
last_updated: 2025-07-20
status: complete
---

# Admin Settings UI

This guide merges the "Admin Configuration UI" and [Settings Page Codex](../settings-page-codex.md) documents.
Administrators can manage dashboard widgets and plugin options from the Settings
pages. The interface uses tabbed navigation and integrates with the WordPress
menu system.

## Key Features
- Manage Widget Block availability per role
- Lock specific widgets for all users
- Organize settings into tabs such as General, Import/Export, Backup and Updates
- Examples provided for PHP hooks and React components
## Settings Tabs Explained

Each section of the **ArtPulse → Settings** screen is organized into a tab. The following descriptions outline the default tabs provided by the plugin:

### General
- Basic plugin options such as enabling widgets, setting default page slugs and toggling debug output.
- Changes take effect immediately after clicking **Save Settings**.

### Import/Export
- Tools for migrating configuration between sites.
- The **Export Settings** button downloads a JSON file with your current options.
- Use **Import Settings** to upload the file on another installation. The form submits via AJAX and displays a success notice without reloading the page.

### Config Backup
- Allows administrators to create periodic backups of the entire options table.
- Press **Create Backup** to generate a timestamped `.zip` archive in `wp-content/backups`.
- The **Restore** dropdown lists available backups and restores them via AJAX when selected.

### Updates
- Controls automatic updates and version checks.
- When an update is available the tab shows a notice with the changelog link.
- Clicking **Update Now** runs the update routine in the background and outputs progress messages through AJAX polling.

### Shortcode Pages
- Lists pages that embed plugin shortcodes.
- Quickly jump to edit screens or insert new shortcode blocks using the **Add Page** shortcut.

```html
<!-- Example tabbed layout -->
<div class="wrap">
  <h2 class="nav-tab-wrapper">
    <a class="nav-tab nav-tab-active" href="#general">General</a>
    <a class="nav-tab" href="#import-export">Import/Export</a>
    <a class="nav-tab" href="#backup">Config Backup</a>
    <a class="nav-tab" href="#updates">Updates</a>
  </h2>
  <form id="artpulse-settings">
    <!-- form fields -->
    <p class="submit"><button class="button-primary">Save Settings</button></p>
  </form>
</div>
```

Settings are saved using the WordPress options API. Most forms submit with `wp_ajax` handlers so the page does not refresh. Success and error messages appear inline next to the form controls.
## Settings Overview

Below is a summary of the default configuration tabs and the fields found within each. Screenshots use placeholders so you can replace them with real images when documenting your own setup.

### General
![General Tab](images/settings-general.png)

| Setting | Description | Default |
|---------|-------------|---------|
| **UI Mode** | Choose between the legacy "Salient" templates or the React-based dashboard. | `salient` |
| **Theme** | Select the default CSS theme used for front‑end components. | `default` |
| **Enable Reporting** | Toggles analytics collection and dashboard widgets. Only visible to administrators. | Enabled |
| **Admin Email** | Address used for notification emails. | WordPress admin email |
| **Use WP Nav Menu** | If enabled, plugin pages automatically appear in the WordPress menus. | Disabled |

### Import/Export
![Import Export Tab](images/settings-import-export.png)

| Setting | Description |
|---------|-------------|
| **Export Settings** | Download a JSON backup of all plugin options. |
| **Import Settings** | Upload a previously exported file. Only administrators can run this action. |

### Config Backup
![Backup Tab](images/settings-backup.png)

| Setting | Description |
|---------|-------------|
| **Create Backup** | Generate a timestamped zip archive of the current configuration. |
| **Restore Backup** | Choose an existing backup to restore. |

### Updates
![Updates Tab](images/settings-updates.png)

| Setting | Description |
|---------|-------------|
| **Automatic Updates** | Enable background updates when a new version is released. |
| **Update Now** | Manually trigger the update routine. Administrators only. |

### Shortcode Pages
![Shortcodes Tab](images/settings-shortcodes.png)

This tab lists every page that embeds one of the plugin shortcodes. Users with the `edit_pages` capability can jump directly to a page editor or insert new blocks using the **Add Page** shortcut.

---
Settings are visible to users with the `manage_options` capability unless otherwise noted. Most fields default to the values shown above and take effect immediately after clicking **Save Settings**.

💬 Found something outdated? Submit Feedback
