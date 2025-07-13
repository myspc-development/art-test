# ArtPulse Codex: UI/UX Polish

This guide outlines optional modules for customizing dashboards, branding the plugin and guiding users through onboarding and contextual help. It is intended for developers implementing or extending these user experience features.

## 1. Customizable Dashboards

Widgets on user dashboards are rendered as individual components. A small registry defines each widget ID, label and required capability. User preferences are stored in `ap_dashboard_layout` user meta as an ordered list of widget IDs. A JavaScript drag‑and‑drop library such as SortableJS can update the order. Layout changes are saved via AJAX and loaded whenever the dashboard renders. Developers may register additional widgets with the `artpulse_register_dashboard_widget` hook. Call `DashboardWidgetRegistry::register( $id, $callback, $capability )` inside that hook to make widgets available in the editor.
See the [Widget Settings Codex](widget-settings-codex.md) for schema details and per-user storage.

```php
add_action('artpulse_register_dashboard_widget', function () {
    \ArtPulse\Core\DashboardWidgetRegistry::register(
        'my-widget',
        'my_widget_callback',
        'view_artpulse_dashboard'
    );
});
```

`DashboardWidgetRegistry::register()` accepts the widget slug, a callback that renders
the widget, and an optional capability required to view it. Any callable may be
used for the callback—functions, object methods or an anonymous closure.

Default layouts can be configured in the admin. Visit **ArtPulse → Settings → Dashboard Widgets** to arrange widgets for each role. Definitions are pulled from `ap_get_all_widget_definitions()` and the selections are stored in the `ap_dashboard_widget_config` option. Roles include `member`, `artist`, `organization` and any custom roles you register.

Any role added via WordPress' `add_role()` function will automatically appear in the editor because the settings page reads `wp_roles()->roles`. When using the plugin's role hierarchy you may register roles on activation:

```php
register_activation_hook(__FILE__, function () {
    add_role('curator', 'Curator', ['view_artpulse_dashboard' => true]);
});
```

### Example: Drag‑and‑Drop Editor

The Dashboard Widgets screen outputs two sortable lists named **Available** and
**Active**. SortableJS powers the drag‑and‑drop behavior. A simplified excerpt of
the React component shows the setup:

```jsx
import Sortable from 'sortablejs';

Sortable.create(activeRef.current, { group: 'widgets', animation: 150 });
Sortable.create(availRef.current, { group: 'widgets', animation: 150 });
```

Widgets can be moved between columns and reordered. Clicking **Save** persists
the layout to `ap_dashboard_widget_config` via AJAX.

### Widget Editor Enhancements

Recent updates add small quality-of-life features for admins arranging the
dashboard:

- Each entry in the **Add Widget** panel now includes a live preview rendered by
  `DashboardWidgetTools::render_widget_preview()`, giving a quick look at the
  widget output.
- A search box filters available widgets as you type so large sites remain
  manageable.
- Widget cards display a hamburger-style drag handle icon to signal that they
  can be reordered via drag and drop.

## 2. White‑Labeling

Organizations can brand the dashboard and emails. A **Branding** tab under **ArtPulse → Settings** allows admins to upload a logo, choose primary and secondary colors and set email footer details. These settings are stored in site options and injected as CSS variables when the dashboard loads. Email templates swap in the logo and colors so that outbound notifications match the chosen branding. Filters like `artpulse_branding_logo_url` let developers override values.

## 3. Onboarding Flows

New users may be guided through setup screens after their first login. Onboarding steps are tracked in `ap_onboarding_steps` user meta and marked complete in `ap_onboarding_completed` once finished. The flow may include profile setup, creating the first event and connecting payment providers. Popover hints appear contextually as the user navigates. Templates such as `templates/onboarding-artist.php` can be customized and additional steps are added via the `artpulse_onboarding_steps` filter.

## 4. Contextual Help

Tooltips and help modals provide quick guidance throughout the dashboard. A registry of help snippets is maintained with `artpulse_register_help_item()`. Front‑end scripts display a help icon next to fields and widgets; clicking opens a modal with detailed instructions or contact links. Content can be overridden or translated via the `artpulse_help_content` filter. Optional integration with a chat provider keeps real‑time assistance one click away.

## 5. Settings Registry

The admin settings screen now uses a tabbed layout. Tabs and their fields are
collected by a simple `SettingsRegistry` class before rendering. Managers call
`SettingsRegistry::register_tab( $slug, $label )` to add a tab and
`SettingsRegistry::register_field( $tab, $key, $config )` for each field.

```php
use ArtPulse\Admin\SettingsRegistry;

SettingsRegistry::register_tab( 'membership', __( 'Membership', 'artpulse' ) );
SettingsRegistry::register_field( 'membership', 'default_privacy_email', [
    'label'   => __( 'Default Email Privacy', 'artpulse' ),
    'type'    => 'select',
    'options' => [ 'public' => 'Public', 'private' => 'Private' ],
] );
```

Filters let developers modify these arrays before output:

- `artpulse_settings_tabs` adjusts the final list of tab slugs and labels.
- `artpulse_settings_fields_{slug}` filters the fields for a given tab.

All registered tabs appear in a horizontal navigation bar. Selecting a tab shows
only the associated form section.

## Best Practices

All UI strings should be translatable. Save branding and layout changes via AJAX for instant feedback. Onboarding and help flows must be dismissible and accessible on mobile and for screen readers.

## Performance Optimization

Avoid synchronous layout reads and writes that trigger forced reflows. Separate
DOM measurements from style changes:

```js
// Bad: read and write intermixed
const height = element.offsetHeight;
element.style.height = height + 10 + 'px';

// Good: split the write into a new frame
const currentHeight = element.offsetHeight;
requestAnimationFrame(() => {
  element.style.height = currentHeight + 10 + 'px';
});
```

Batch multiple updates in `requestAnimationFrame` or `requestIdleCallback` and
debounce expensive listeners for smoother scrolling and interaction.

### Font Optimization

On slow connections or internal networks, host fonts locally rather than using
Google Fonts. Serve the files from `assets/fonts/` and declare them with
`@font-face`:

```css
@font-face {
  font-family: 'Open Sans';
  src: url('../fonts/OpenSans-Regular.woff2') format('woff2');
  font-display: swap;
}
```

Use `font-display: swap` so text is visible while fonts load.
