---
title: Admin Usage Guide
category: admin
role: admin
last_updated: 2025-07-20
status: complete
---
# Admin Usage Guide

This document explains how site administrators manage role‑based dashboard layouts.

## Editing Layouts
1. In the WordPress dashboard open **ArtPulse → Dashboard Builder**.
2. Choose a role from the dropdown.
3. Drag widgets to reorder or use the eye icon to toggle visibility.
4. Click **Save Layout** to store the configuration.

## Resetting Layouts
- Use **Reset Layout** in the manager to restore defaults for the selected role.
- Individual users can reset their personal layout from their dashboard using the "♻ Reset My Dashboard" button.

## wp-admin Access

Use the **Allow wp-admin Access** option under **ArtPulse → Settings → General** to control whether members, artists, and organizations are redirected to the custom dashboards or allowed into the WordPress admin area. Grant the `view_wp_admin` capability to specific roles or users if only a subset should bypass the redirect.

## Import / Export
- Administrators can export the current layout JSON using the **Export** button.
- Paste JSON into the import field and click **Import Layout** to restore a configuration.

> 💬 *Found something outdated? [Submit Feedback](../../feedback.md)*
