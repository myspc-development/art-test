---
title: Gutenberg Dashboard Editor
category: developer
role: developer
last_updated: 2025-07-27
status: complete
---
# Gutenberg Dashboard Editor

This document describes the block‑based dashboard implementation. Widgets are now registered as Gutenberg blocks instead of custom PHP callbacks.

## Block Registration

Each dashboard widget lives under `blocks/` with a `block.json` file and optional `editor.js`. Blocks are registered on `init` via classes in `src/Blocks/`. Example:

```php
\ArtPulse\Blocks\DashboardNewsBlock::register();
```

The block uses a render callback that outputs the same template used by the legacy widget.

## Layout Storage

Default layouts are saved in the `ap_dashboard_widget_config` option as arrays of widget IDs keyed by role. During `init` these arrays are loaded so the editor can present the default order for each role.

Per‑user layouts use the same array format stored in user meta.

## Editing

Administrators can compose dashboards using the block editor and save the resulting widget list to `ap_dashboard_widget_config`. Users receive the list for their role unless they have customized their own layout.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
