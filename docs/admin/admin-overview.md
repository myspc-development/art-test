---
title: Admin Overview
category: admin
role: developer
last_updated: 2025-07-20
status: complete
---

# Admin Overview

The overview summarizes the main Dashboard Modules available to administrators.

## RSVP Management
- Open the event in your dashboard and click **View RSVPs**.
- Mark attendees as **Attended** or leave unchecked.
- Use **Export RSVPs to CSV** to download responses.

## Attendee Messaging
- Click **Message All** to email every RSVP'd user.
- Use the message icon next to any attendee to send a single message.
- Templates are configured under **ArtPulse → Settings**.

## Event Analytics
- Dashboards visualize total RSVPs, favorites and attendance.
- Charts show trends over time and top events.
- Export CSV reports from the **Snapshots** submenu.

## Settings Tabs
All options live under **ArtPulse → Settings**. Tabs such as **General**, **Location APIs**, **Import/Export**, **Config Backup**, **Updates** and **Shortcode Pages** divide the settings.

## PWA Manifest File
The plugin includes a `manifest.json` file in its root directory. The helper
script `pwa-manifest.php` injects a `<link rel="manifest" href="/manifest.json">`
tag into the page head so browsers can locate it. If your WordPress install
uses custom routing or lives in a subdirectory, ensure the `/manifest.json`
request maps to the plugin's copy. You may need to add a rewrite rule or copy
the file to your site root so install prompts work correctly.


## Additional Notes
- The tools are responsive and keyboard accessible.
- After plugin updates visit **Settings → Permalinks** to flush rewrite rules if needed.
- After activation resave your permalinks to ensure the `/dashboard-role` preview page works.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
