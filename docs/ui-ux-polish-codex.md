# ArtPulse Codex: UI/UX Polish

This guide outlines optional modules for customizing dashboards, branding the plugin and guiding users through onboarding and contextual help. It is intended for developers implementing or extending these user experience features.

## 1. Customizable Dashboards

Widgets on user dashboards are rendered as individual components. A small registry defines each widget ID, label and required capability. User preferences are stored in `ap_dashboard_layout` user meta as an ordered list of widget IDs. A JavaScript drag‑and‑drop library such as SortableJS can update the order. Layout changes are saved via AJAX and loaded whenever the dashboard renders. Developers may register additional widgets with the `artpulse_register_dashboard_widget` hook.

```php
add_action('artpulse_register_dashboard_widget', function () {
    \ArtPulse\Core\DashboardWidgetRegistry::register(
        'my-widget',
        '__return_null',
        'view_artpulse_dashboard'
    );
});
```

Default layouts can be configured in the admin. Visit **ArtPulse → Settings → Dashboard Widgets** to arrange widgets for each role. Definitions are pulled from `ap_get_all_widget_definitions()` and the selections are stored in the `ap_dashboard_widget_config` option. Roles include `member`, `artist`, `organization` and any additional roles registered with `RoleSetup`.

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
