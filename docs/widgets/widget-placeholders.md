---
title: Widget Placeholders
category: widgets
role: developer
last_updated: 2025-08-29
status: draft
---

# Widget Placeholders

Some widgets may be missing or not yet implemented. When this occurs the plugin can render a placeholder so layouts remain stable.

Placeholders are loaded by `ap_placeholder_bootstrap()` on the `plugins_loaded` hook. They are **enabled by default** via the `AP_ENABLE_WIDGET_PLACEHOLDERS` constant or the `ap_enable_widget_placeholders` option.

## Disabling Placeholders

Use the `ap_widget_placeholder_enabled` filter to turn the feature off:

```php
add_filter( 'ap_widget_placeholder_enabled', '__return_false' );
```

## Debugging

For debugging you can inspect the data passed to placeholders by filtering `ap_widget_placeholder_debug_payload`.

```php
add_filter( 'ap_widget_placeholder_debug_payload', function ( $args, $id ) {
    $args['debug'] = $id;
    return $args;
}, 10, 2 );
```

