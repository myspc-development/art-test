---
title: Developer Structure Guide
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Developer Structure Guide

This document provides an overview of the plugin code layout and how to extend it.

## Folder Layout

- `src/` – PHP classes organized by namespace (e.g. `ArtPulse\Blocks`, `ArtPulse\Rest`).
- `includes/` – Helper functions and legacy procedural code.
- `templates/` – PHP templates for widgets and front‑end views.
- `assets/` – JavaScript and CSS assets compiled for production.
- `blocks/` – Block registration metadata (`block.json`) and render callbacks.
- `tests/` – PHPUnit test suite.

## Adding Shortcodes

1. Create a class or function under `src/Frontend` or `includes`.
2. Call `add_shortcode` in your registration method.
3. Sanitise all attributes and escape output.

## Adding Widgets and Blocks

- Traditional dashboard widgets are rendered via templates in `templates/widgets` and registered in `includes/dashboard-widgets.php`.
- Block based widgets live under `blocks/` with a `block.json` file and optional `editor.js` for Inspector controls. Register them in `src/Core/Plugin.php` using `register_block_type_from_metadata`.

## Enqueueing Scripts and Styles

Use `wp_enqueue_script` and `wp_enqueue_style` with a version based on `filemtime()` to avoid cache issues. Localise scripts with `wp_localize_script` to pass dynamic data such as REST URLs and nonces.

## Registering REST Endpoints

Create a controller class under `src/Rest/` with a `register()` method hooking into `rest_api_init`. Use `register_rest_route()` to define endpoints and return data via `rest_ensure_response()`.
