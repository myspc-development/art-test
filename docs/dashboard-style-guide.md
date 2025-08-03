---
title: Dashboard Style Guide
category: developer
role: developer
last_updated: 2025-09-15
status: complete
---
# Dashboard Style Guide

Use the unified styles defined in the [Default Design System Codex](default-design-system-codex.md) when creating or updating dashboard widgets.

## Standard Classes

Use these standard classes for all dashboards, widgets, and forms:

- `.ap-dashboard` – wrapper for dashboard layouts
- `.ap-widget` – card or widget container
- `.ap-widget-header` – optional header section inside a widget
- `.ap-widget-body` – body content of a widget
- `.ap-widget-card` – standard wrapper for dashboard widgets
- `.ap-widget-icon` – small icon displayed in the header
- `.ap-widget-title` – widget title element
- `.ap-widget-controls` – wrapper for toggle/remove buttons
- `.ap-widget-content` – main widget output area
- `.ap-widget-tag` – visual tag or category label
- `.dashboard-nav` – dashboard navigation links
- `.ap-form-container` – form wrapper
- `.ap-form-label` – form labels
- `.ap-input` – form inputs and textareas
- `.ap-form-button` – buttons within forms or dashboards

No other CSS files should be loaded or referenced. Any new shortcodes or features must use these classes so the UI remains consistent.

Layouts should be constructed using WPBakery row and column elements. The Salient theme provides additional utility classes that can be combined with the plugin‑specific classes above.

## Widget Structure

- Wrap widget content in `.ap-card` and include a heading element with the class `.ap-card__title`.
- Import the token file at the top of each SCSS module using `@use "../css/tokens.css";`.
- Register widgets through `DashboardWidgetRegistry::register()` so markup and styles load automatically.
- Widgets may declare a `settings` schema and default `width`/`height` values. These show in the builder's configuration modal. Keep widths within the 12‑column grid.
- Rebuild assets with `npm run build` whenever you modify files under `src/` or `blocks/` and commit the updated `build/` output.

These steps ensure every widget follows the same layout, theming variables, and accessibility hooks.

## Authoritative Styling

Use the custom `.ap-card` styling as the authoritative pattern for all widgets. The WordPress `.dashboard-widget` class is deprecated and should not be used in new development.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
