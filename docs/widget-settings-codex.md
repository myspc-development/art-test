# Widget Settings Codex

This guide explains how dashboard widgets are defined, configured and stored for each user.

## 1. Widget Definitions

`ap_get_all_widget_definitions()` returns an array of widget metadata filtered by `ap_dashboard_widget_definitions`. Each entry contains an ID, name, icon and description:

```php
function ap_get_all_widget_definitions(): array {
    $defs = [
        [ 'id' => 'membership',      'name' => __('Membership', 'artpulse'),
  'icon' => 'users',      'description' => __('Subscription status and badges.', 'artpulse') ],
        [ 'id' => 'upgrade',         'name' => __('Upgrade', 'artpulse'),
  'icon' => 'star',       'description' => __('Upgrade options for the account.', 'artpulse') ],
        [ 'id' => 'local-events',    'name' => __('Local Events', 'artpulse'),
  'icon' => 'map-pin',    'description' => __('Shows events near the user.', 'artpulse') ],
        [ 'id' => 'favorites',       'name' => __('Favorites', 'artpulse'),
  'icon' => 'heart',      'description' => __('Favorited content lists.', 'artpulse') ],
        ...
    ];
    return apply_filters('ap_dashboard_widget_definitions', $defs);
}
```

## 2. Registering Widgets

Developers can expose new widgets by hooking into `artpulse_register_dashboard_widget` and calling `DashboardWidgetRegistry::register()`:

```php
add_action('artpulse_register_dashboard_widget', function () {
    \ArtPulse\Core\DashboardWidgetRegistry::register(
        'my-widget',
        'my_widget_callback',
        'view_artpulse_dashboard'
    );
});
```

`DashboardWidgetRegistry::register()` accepts the widget slug, a callback that renders the widget and an optional capability requirement.

## 3. Default Layout Configuration

Admins arrange default widget layouts under **ArtPulse → Settings → Dashboard Widgets**. The selected order for each role is stored in the `ap_dashboard_widget_config` option. Changes are saved via AJAX:

```php
add_action('wp_ajax_ap_save_dashboard_widget_config', 'ap_save_dashboard_widget_config');

function ap_save_dashboard_widget_config(): void {
    check_ajax_referer('ap_dashboard_widget_config', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission denied', 'artpulse')]);
    }
    $raw = $_POST['config'] ?? [];
    $sanitized = [];
    foreach ($raw as $role => $widgets) {
        $role_key = sanitize_key($role);
        $ordered = [];
        foreach ((array) $widgets as $w) {
            $ordered[] = sanitize_key($w);
        }
        $sanitized[$role_key] = $ordered;
    }
    update_option('ap_dashboard_widget_config', $sanitized);
    wp_send_json_success(['saved' => true]);
}
```

## 4. Per‑User Preferences

The REST endpoint `/artpulse/v1/ap_dashboard_layout` loads and saves each user's preferences. When no user meta exists, the defaults from `ap_dashboard_widget_config` are used:

```php
register_rest_route('artpulse/v1', '/ap_dashboard_layout', [
    'methods'  => 'GET',
    'callback' => [ self::class, 'getDashboardLayout' ],
    'permission_callback' => fn() => is_user_logged_in(),
]);

register_rest_route('artpulse/v1', '/ap_dashboard_layout', [
    'methods'  => 'POST',
    'callback' => [ self::class, 'saveDashboardLayout' ],
    'permission_callback' => fn() => is_user_logged_in(),
    'args'     => [
        'layout'     => [ 'type' => 'array',  'required' => false ],
        'visibility' => [ 'type' => 'object', 'required' => false ],
    ],
]);

public static function getDashboardLayout() {
    $uid   = get_current_user_id();
    $layout = get_user_meta($uid, 'ap_dashboard_layout', true);
    if (!is_array($layout) || empty($layout)) {
        $roles  = wp_get_current_user()->roles;
        $config = get_option('ap_dashboard_widget_config', []);
        foreach ($roles as $r) {
            if (!empty($config[$r]) && is_array($config[$r])) {
                $layout = array_map('sanitize_key', $config[$r]);
                break;
            }
        }
        if (!is_array($layout)) {
            $layout = [];
        }
    }
    $vis = get_user_meta($uid, 'ap_widget_visibility', true);
    if (!is_array($vis)) {
        $vis = [];
    }
    return rest_ensure_response([
        'layout'     => $layout,
        'visibility' => $vis,
    ]);
}

public static function saveDashboardLayout( WP_REST_Request $request ) {
    $uid = get_current_user_id();
    if ($request->has_param('layout')) {
        $layout = array_map('sanitize_text_field', (array) $request->get_param('layout'));
        update_user_meta($uid, 'ap_dashboard_layout', $layout);
    }
    if ($request->has_param('visibility')) {
        $vis_raw = (array) $request->get_param('visibility');
        $vis = [];
        foreach ($vis_raw as $key => $val) {
            $vis[sanitize_key($key)] = (bool) $val;
        }
        update_user_meta($uid, 'ap_widget_visibility', $vis);
    }
    return rest_ensure_response(['saved' => true]);
}
```

User meta keys used:

- `ap_dashboard_layout` – ordered list of widget IDs.
- `ap_widget_visibility` – array of booleans for each widget.

## 5. Resetting to Defaults

The dashboard includes a **Reset Layout** button. Its click handler clears local storage and posts empty settings so the next load falls back to the defaults:

```javascript
const resetBtn = document.getElementById('ap-reset-layout');
if (resetBtn) {
  resetBtn.addEventListener('click', () => {
    const msg = apL10n?.reset_confirm || 'Reset dashboard layout?';
    if (!confirm(msg)) return;
    localStorage.removeItem('apDashboardLayout');
    localStorage.removeItem('apWidgetVisibility');
    fetch(`${ArtPulseDashboardApi.root}artpulse/v1/ap_dashboard_layout`, {
      method: 'POST',
      headers: { 'X-WP-Nonce': ArtPulseDashboardApi.nonce, 'Content-Type': 'application/json' },
      body: JSON.stringify({ layout: [], visibility: {} })
    }).finally(() => window.location.reload());
  });
}
```

## 6. Widget Settings

Each widget may expose configurable options. The `DashboardWidgetRegistry::register()`
method accepts a `settings` array describing each field:

```php
DashboardWidgetRegistry::register(
    'favorites',
    __('Favorites', 'artpulse'),
    'heart',
    __('Favorited content lists.', 'artpulse'),
    'ap_widget_favorites',
    'view_artpulse_dashboard',
    [
        [ 'key' => 'limit', 'label' => __('Items to Show', 'artpulse'), 'type' => 'number', 'default' => 5 ]
    ]
);
```

Settings are stored in `ap_widget_settings_{id}` user meta or options when the
`global` flag is provided. The REST endpoint `/artpulse/v1/widget-settings/{id}`
loads and saves values:

```php
// Get current user settings
GET  /wp-json/artpulse/v1/widget-settings/favorites

// Save settings for the user
POST /wp-json/artpulse/v1/widget-settings/favorites
{
  "settings": { "limit": 10 }
}

// Administrators can pass ?global=1 to modify site defaults
```

Responses include the field schema and current values so the dashboard can
render a simple form.
