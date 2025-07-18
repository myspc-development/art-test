# Dashboard Editor Developer Guide

This guide describes the React and SortableJS implementation of the admin dashboard editor.

## Interface Overview
- Role selector switches between **member**, **artist** and **organization** layouts.
- Widgets are displayed in draggable cards powered by [SortableJS](https://sortablejs.github.io/Sortable/).
- The editor provides **Add Widget**, **Save**, **Reset**, **Export** and **Import** controls.

## Key Scripts
- `assets/js/admin-dashboard-widgets-editor.js` renders the React UI.
- Layout data is fetched from `/wp-json/artpulse/v1/ap_dashboard_layout` with a `?user_id=0&role={role}` query when editing defaults.
- Updates post to the `ap_save_dashboard_widget_config` AJAX action using a nonce created on page load.

## Live Preview
The bottom of the editor embeds the `[ap_user_dashboard role="{role}"]` shortcode. As you reorder or toggle visibility, the preview updates automatically so admins can see the exact layout users will see.

## Adding Widgets
Open the **Add Widget** modal to search registered widgets by name or category. Each result shows a label, description and preview image when available. Selecting widgets appends them to the layout in the current order.

For more on widget definitions see the [Widget Registry Reference](./widget-registry-reference.md).
