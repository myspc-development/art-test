# Widget Editor Codex Instructions

This document explains how to incorporate new dashboard widgets and layout features into the codex. Use it as a checklist when documenting or implementing updates to the drag‑and‑drop editor.

## 1. Review the Widget Docs

Before adding or modifying widgets, read the following references:

- [Dashboard Editor Developer Guide](./dashboard-editor-developer-guide.md)
- [Widget Manager Data Layer Guide](./widget-manager-data-layer-guide.md)
- [Widget Registry Reference](./widget-registry-reference.md)
- [REST API & AJAX Endpoint Specification](./rest-api-and-ajax-endpoint-spec.md)
- [Widget Layout Import & Export Guide](./widget-layout-import-export-guide.md)
- [Widget Manager Testing Checklist](./widget-manager-testing-checklist.md)

These files cover layout storage, endpoints, the React interface and testing steps. Reviewing them ensures your codex additions stay consistent.

## 2. Document New Widgets

When you register a new widget with `DashboardWidgetRegistry::register()`, add a short section to the codex describing:

1. The widget ID, label and category.
2. Any settings fields or options.
3. Example JSON from the layout export.

Listing this information helps future contributors understand how the widget integrates with the editor and where defaults are stored.

## 3. Update Layout Examples

If you change the default layout or add new visibility options, update the sample JSON in the [Widget Layout Import & Export Guide](./widget-layout-import-export-guide.md) and note the change here. Include the role the layout applies to and any special visibility flags.

## 4. Keep User Docs in Sync

Whenever the editor gains new controls or widgets, remember to update the [Admin Dashboard Widgets Editor Guide](./Admin_Dashboard_Widgets_Editor_Guide.md) so non‑technical admins can follow along. Link back to that guide from your codex section if necessary.

## 5. Reference in the Index

After writing or revising widget editor notes, add a link in `codex_index.md` under the **Dashboard Widget Manager** table so developers can locate the instructions quickly.
