---
title: Settings Page Codex
category: admin
role: developer
last_updated: 2025-07-20
status: complete
---

# Settings Page Codex

The admin settings screen collects tab and field definitions from multiple modules via a shared registry. Each tab groups related options and is displayed using a horizontal navigation menu.

## Registering Tabs

Managers call `SettingsRegistry::register_tab()` early in the plugin bootstrapping process. A slug and human‑readable label are stored for later output.

```php
use ArtPulse\Admin\SettingsRegistry;

SettingsRegistry::register_tab( 'widgets', __( 'Widget Editor', 'artpulse' ) );
```

## Registering Fields

Individual options are added under a tab with `SettingsRegistry::register_field()`. The field configuration includes the label, description and input type.

```php
SettingsRegistry::register_field( 'widgets', 'enable_editor', [
    'label' => __( 'Enable Widget Editor', 'artpulse' ),
    'type'  => 'checkbox',
] );
```

All tabs and fields can be modified via the `artpulse_settings_tabs` and `artpulse_settings_fields_{slug}` filters.

## Rendering the Page

`SettingsPage::register()` boots the registry and hooks into WordPress to render the tabs and fields.

```php
use ArtPulse\Admin\SettingsPage;

SettingsPage::register();
```

During `registerSettings()` the page iterates over the collected tabs and fields, creating sections and calling `add_settings_field()` for each option.

```
$tabs = apply_filters( 'artpulse_settings_tabs', SettingsRegistry::get_tabs() );
foreach ( $tabs as $slug => $label ) {
    $fields = apply_filters( 'artpulse_settings_fields_' . $slug, SettingsRegistry::get_fields( $slug ) );
    foreach ( $fields as $key => $config ) {
        add_settings_field( $key, $config['label'], [ SettingsPage::class, 'renderField' ], 'artpulse-' . $slug, $section, [
            'label_for'   => $key,
            'description' => $config['desc'] ?? '',
            'field'       => $config,
            'tab'         => $slug,
        ] );
    }
}
```

With the registry populated and `SettingsPage::register()` executed, the settings screen will display each registered tab and its fields automatically.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
