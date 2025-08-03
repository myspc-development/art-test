# Widget Architecture

This plugin exposes a modular dashboard widget system. Widgets may be rendered entirely in PHP or mounted as React components.

## Widget Types

| Type | Description |
| ---- | ----------- |
| **PHP** | Rendered on the server with a PHP class or function. |
| **React** | Rendered by JavaScript. PHP outputs a placeholder container and a React component is mounted client‑side. |

## Discovery

Widgets are registered in `includes/dashboard-widgets.php` and described in `widget-manifest.json`. These sources allow the dashboard to enumerate available widgets and their assets.

## PHP Widget Interface

PHP widgets should expose a callable that returns a string of markup. Member widgets live in `widgets/member/` and typically provide a static `id()` and a `render()` method.

## Rendering React Widgets

Use `ap_render_js_widget( $id, $props = [] )` to output a mount point:

```php
<?php ap_render_js_widget( 'rsvp_button', [ 'eventId' => 123 ] ); ?>
```

This generates:

```html
<div id="ap-widget-rsvp_button" data-widget="rsvp_button" data-props='{"eventId":123}'></div>
```

`assets/js/widgets.js` scans for elements with `data-widget` and dynamically imports the matching `XyzWidget` component from `assets/js/widgets/`.

## Creating a Widget

1. **PHP only:** create `widgets/member/MyWidget.php` and register it in `includes/dashboard-widgets.php`.
2. **React:**
   - Create `assets/js/widgets/MyWidgetWidget.jsx` exporting a React component.
   - Output it from PHP with `ap_render_js_widget( 'my_widget', $props )`.

## Lifecycle Example

| Phase | PHP Widget | React Widget |
| ----- | ---------- | ------------ |
| Register | Define class and call `register_ap_widget()` | Same as PHP widget |
| Render | PHP returns full markup | `ap_render_js_widget()` outputs container |
| Client Mount | N/A | `widgets.js` imports `MyWidgetWidget` and mounts it with props |

## Known Member Widgets

| Widget ID | Type | PHP Class / JS Component | Status |
| --------- | ---- | ------------------------ | ------ |
| welcome_box | PHP | `WelcomeBoxWidget` | ✅ |
| event_chat | React | `EventChatWidget` (via JS mount) | 🔧 stub |

## Conventions

- Widget PHP files live in `widgets/member/`.
- React components are named `XyzWidget` and reside in `assets/js/widgets/`.
- `data-widget` attributes use kebab‑case widget IDs.
