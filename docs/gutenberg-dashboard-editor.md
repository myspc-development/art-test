---
title: Gutenberg Dashboard Editor
category: developer
role: developer
last_updated: 2025-07-20
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

Default layouts are saved in the `ap_dashboard_widget_config` option as block pattern markup keyed by role. During `init` the `DashboardBlockPatternManager` registers these patterns so they appear in the block editor under the **Dashboards** category.

Per‑user layouts can still be stored in user meta, but the markup is saved instead of an array of IDs.

## Editing

Administrators can compose dashboards using the block editor and save the generated markup to `ap_dashboard_widget_config`. Users receive the pattern for their role unless they have customized their own layout.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
