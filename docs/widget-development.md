# Widget Development

This project uses small dashboard widgets to surface features in the member and organization dashboards. Widgets must implement the [`DashboardWidgetInterface`](../src/Core/DashboardWidgetInterface.php) which defines the required methods:

```php
interface DashboardWidgetInterface {
    public static function id(): string;       // Unique slug
    public static function label(): string;    // Widget title
    public static function roles(): array;     // Allowed roles
    public static function description(): string; // Short summary
    public static function render(): string;   // Return HTML output
}
```

## Renderable PHP widgets

A PHP widget returns the complete markup from its `render()` method. Use the boilerplate in `/boilerplate` as a starting point:

```php
class ExampleWidget implements DashboardWidgetInterface {
    // ...id(), label(), roles(), description()...

    public static function render(): string {
        if ( ! is_user_logged_in() ) {
            return '<div class="notice notice-error"><p>' . esc_html__( 'You do not have access.', 'artpulse' ) . '</p></div>';
        }

        ob_start();
        include __DIR__ . '/example-widget-template.php';
        return ob_get_clean();
    }
}
```

## Lazy‑loaded React widgets

React widgets should render a container element and load the React bundle separately. Mark the widget as lazy in `config/dashboard-widgets.php` so scripts are enqueued only when needed.

```php
public static function render(): string {
    return '<div id="my-react-widget"></div>';
}
```

Your JavaScript can then mount the component:

```jsx
import { createRoot } from 'react-dom/client';
import MyWidget from './MyWidget.jsx';

const root = createRoot( document.getElementById( 'my-react-widget' ) );
root.render( <MyWidget /> );
```

## Layout and markup

WordPress dashboard widgets should follow the core structure so they inherit
default styling:

```html
<div class="postbox">
  <h2 class="hndle"><span>Widget Title</span></h2>
  <div class="inside">
    <!-- content -->
  </div>
</div>
```

Use the `.inside` container for padding and align form elements with the
standard `.regular-text` and `.button` classes. Avoid custom margins so widgets
remain visually consistent.

When including icons prefer Dashicons and mark them with `aria-hidden="true"`
or provide screen reader text to maintain accessibility.

## Assigning widgets to roles

Register widgets for specific roles by providing a `roles` array or using the helper `register_widget_for_roles`:

```php
use ArtPulse\Core\DashboardWidgetRegistry;

DashboardWidgetRegistry::register_widget_for_roles(
    'example_widget',
    [
        'label'    => 'Example',
        'callback' => [ ExampleWidget::class, 'render' ],
    ],
    [ 'member', 'artist' ]
);
```

For JSON configuration files, include the same `roles` key:

```json
{
  "id": "example_widget",
  "roles": ["member"],
  "file": "widgets/example-widget.php"
}
```

## Best practices

* **Escaping:** Always escape output using `esc_html`, `esc_attr` or `wp_kses_post`.
* **Caching:** Expensive widgets can opt‑in to caching by setting `'cache' => true` in their config entry and leveraging transients or the object cache.
* **User role checks:** Implement `roles()` and optional `can_view()` logic to ensure only authorized users can view widget data.

Consult the `/boilerplate` directory for a sample widget class, template and configuration entry that demonstrate these patterns.
