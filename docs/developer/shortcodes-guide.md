---
title: Shortcodes Guide
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Shortcodes Guide

This reference lists the key shortcodes bundled with the plugin. Each section documents syntax, available attributes and an example of what the output looks like.

## `[ap_user_dashboard]`
Renders the current user's dashboard with widgets.

**Options**
- `preset` – load a predefined widget layout.

**Example**
```html
[ap_user_dashboard preset="artist"]
```
The shortcode outputs the draggable dashboard container for the logged‑in user.

## `[ap_widget]`
Embeds a single dashboard widget anywhere on the site.

**Options**
- `id` – widget identifier from the registry.

**Example**
```html
[ap_widget id="stats"]
```
Outputs only the `stats` widget if the current user has permission to view it.

## `[ap_org_dashboard]`
Displays the organization management dashboard with event and artwork lists.

**Example**
```html
[ap_org_dashboard]
```
Only users assigned to an organization will see data.

## `[ap_event_comments]`
Shows the comment list and form for an event post.

**Options**
- `id` – event post ID.

## `[ap_artist_comments]`
Similar to `ap_event_comments` but for artist posts.

## `[ap_recommendations]`
Lists recommended posts based on activity.

**Options**
- `type` – `event` or `artwork` (default `event`)
- `limit` – number of items to show (default `6`)

## `[ap_collection]`
Outputs a curated collection by post ID.

**Options**
- `id` – collection post ID (required)

## Creating Custom Shortcodes
If your plugin needs bespoke dashboard widgets or data views you can register additional shortcodes. Use `add_shortcode` in a setup function and sanitize attributes with `shortcode_atts`. Output should be returned rather than echoed so you can combine shortcodes inside templates. Remember to enqueue any scripts or styles conditionally to avoid loading assets sitewide. Complex shortcodes may delegate rendering to a PHP template or React component.

## Troubleshooting
- Ensure scripts and styles are enqueued by viewing the page source for expected assets.
- Shortcodes that rely on REST endpoints require permalinks to be enabled.
- If output is blank, check user capabilities and that the `id` attribute references existing content.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*

For a full list of available shortcodes, see the files under [docs/shortcodes](../shortcodes/).

### Deprecated registrations
Earlier releases registered several shortcodes in multiple bootstrap files.
These duplicates have been consolidated. The authoritative logic now lives in
`src/Core/ShortcodeManager.php` and `src/Frontend/WidgetEmbedShortcode.php`.
Legacy helpers such as `widget-embed.php` and `CommunityShortcodeManager.php`
are retained only for reference.
