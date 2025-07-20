---
title: Dashboard Editor Developer Guide
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Dashboard Editor Developer Guide

This document provides implementation details for the drag-and-drop dashboard editor. It builds on the Widget Manager reference and explains how the editor assembles layouts, persists changes and enforces role permissions.

## Architecture Overview
The editor is a React application bundled via Webpack. Widget blocks are registered in PHP and exposed through a REST endpoint. When the editor loads it fetches the registry along with the user's existing layout. Each widget has a unique identifier and configuration data stored in the `wp_usermeta` table.

The layout state is managed by Redux. Drag handles update widget order while resize actions adjust column spans. Changes are debounced and automatically saved through a REST `PUT` request. If the network request fails, the editor surfaces an inline error and reverts the layout.

## Adding New Widgets
To make a widget available in the editor, register it in `widgets/widget-registry.php` with metadata such as title, icon and default width. Provide a React edit component that receives props for settings and dataset results. The registry reference explains available fields. Once registered, the widget automatically appears in the editor's sidebar under the appropriate category.

## Permissions and Capability Checks
Only users with the `manage_dashboard` capability can modify layouts. The capability is mapped to administrators by default but can be granted to custom roles. REST handlers validate the current user before accepting layout updates. Widgets themselves may perform additional checks when rendering.

## Extending the Editor
The editor exposes hooks so plugins can add controls or modify behavior. Use the `ap_dashboard_editor_init` action to enqueue extra scripts. Filters allow you to alter the list of available widgets or restrict categories. For significant UI changes consider forking the React app and submitting a pull request so the team can review design impacts.

## Debugging Tips
Enable `SCRIPT_DEBUG` in `wp-config.php` to load unminified JavaScript. The editor logs layout actions to the browser console when the `AP_DEBUG` constant is true. If widgets fail to render, check the network tab for REST errors and confirm that permalinks are enabled.

With these guidelines you can extend the dashboard editor and keep the user experience consistent across the ArtPulse plugin.
