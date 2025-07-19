# Dashboard Widget Editor Codex

This guide outlines the React interface used to customize ArtPulse dashboards and preview layouts. Follow these notes when implementing or extending the editor UI.

## Objective
Create a customizable Widget Editor UI for admins to design dashboards (Member, Artist, Organization) with live Preview Mode capabilities.

## Core Components
- **Dashboard Type Selector** – tabs or a dropdown to switch between Member, Artist, and Organization dashboards. Persist the selected type via local state or a URL parameter.
- **Widget Palette** – display available widgets pulled from a widget registry JSON or endpoint. Enable drag/drop with `react-grid-layout` or `@dnd-kit/core`.
- **Layout Grid** – central canvas with drag‑resizable widget containers. Widget properties are editable via a right‑side panel.
- **Properties Panel** – context-sensitive editor for the selected widget (e.g., change color, toggle chart type). Provide auto‑save or an “Apply Changes” button.
- **Preview Mode Toggle** – button to switch from Editor Mode to Preview Mode. In Preview Mode the layout is read‑only, UI controls are hidden and widgets render with real or mocked data.

## Data Handling
### Layout Persistence
Store layouts in WordPress options or a custom post type. Example structure:

```json
{
  "member": [...],
  "artist": [...],
  "organization": [...]
}
```

### REST API Endpoints
```php
register_rest_route('artpulse/v1', '/widget-layout', [
  'methods' => ['GET', 'POST'],
  'callback' => 'handle_widget_layout',
  'permission_callback' => fn() => current_user_can('manage_options')
]);
```

## Required Files & Structure
```
src/
  admin/
    WidgetEditorApp.jsx
    WidgetPalette.jsx
    DashboardCanvas.jsx
    PropertiesPanel.jsx
    PreviewToggle.jsx
    mock/
      widgetData.json
    styles/
      widget-editor.css
```

## Development Checklist
- Build reusable widget components (refer to `widgets-reference.md`).
- Implement layout save/load functionality.
- Support conditional rendering for preview mode.
- Implement validation such as required widgets.
- Integrate with the WordPress admin page at `admin.php?page=artpulse-widget-editor`.

## Developer Tips
- Use React Context to manage global editor state (layout, selected widget, mode).
- Store widget metadata centrally (type, default size, config schema).
- Lazy-load heavy widgets in Preview Mode only.
- Use feature flags to hide incomplete widgets.

