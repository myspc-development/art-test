---
title: Widgets Overview
category: widgets
role: developer
last_updated: 2025-07-20
status: complete
---

# Widgets Documentation

This folder collects every guide related to dashboard widgets. New developers should read the documents in the following order:

1. **[Widget Specification](./widget-specification.md)** – introduces the architecture and base interface used by all widgets.
2. **[Widget Registry Reference](./widget-registry-reference.md)** – explains how widgets are registered in PHP and JavaScript.
3. **[Widget Lifecycle](./widget-lifecycle.md)** – details initialization hooks and teardown events.
4. **[Widget Matrix Reference](./widget-matrix-reference.md)** – describes how roles map to widget availability.
5. **[Widget Settings Codex](./widget-settings-codex.md)** – covers per-user settings stored in metadata.
6. **[Widget Layout Import Export Guide](./widget-layout-import-export-guide.md)** – shows how to migrate layouts between installations.

Use these guides alongside the [Widget Manager Data Layer Guide](./widget-manager-data-layer-guide.md) when customizing layouts. The recommended read order ensures you first grasp the core spec before exploring advanced usage and deployment scenarios.
See the [REST API reference](../api/rest-api-reference.md) for endpoints used by widgets. QA should consult the [Dashboard Builder UAT Checklist](../qa/dashboard-builder-uat-checklist.md).

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
