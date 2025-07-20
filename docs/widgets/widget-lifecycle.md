---
title: Widget Lifecycle
category: widgets
role: developer
last_updated: 2025-07-20
status: complete
---

# Widget Lifecycle

This document outlines the full lifecycle of a dashboard widget.

## Create
Define the widget block and provide a React component.

## Register
Call `DashboardWidgetRegistry::register()` during the `init` hook so the widget block becomes available to the editor.

## Configure (Settings)
Widgets can expose options on the **Settings** page. See [Settings Page Codex](../settings-page-codex.md) for how configuration keys are registered.

## Import/Export
Widgets appear in exported layout JSON and are restored via the import tool in the editor.

## Test & Verify
Follow the [Widget QA Checklist](../qa/widget-qa-checklist.md) to validate rendering and role visibility.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
