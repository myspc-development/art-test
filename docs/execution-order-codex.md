# ArtPulse Codex: Execution Order & User Functions

This short guide explains how to safely access the current user in plugin code. WordPress defines `wp_get_current_user()` and `current_user_can()` in `pluggable.php`. Calling these functions before the pluggable file loads results in a fatal error such as:

```
PHP Fatal error: Uncaught Error: Call to undefined function wp_get_current_user()
```

## 1. Use Hooks

Never call these functions at the plugin's global scope. Instead wrap the logic in a callback triggered by a core action.

### Admin logic

```php
add_action('admin_init', function () {
    if ( current_user_can('manage_options') ) {
        // Safe admin-based logic.
    }
});
```

### Frontend or REST logic

```php
add_action('init', function () {
    $user = wp_get_current_user();
    // Load any role specific setup here.
});
```

## 2. Secure REST Endpoints

When registering routes always provide a `permission_callback` so that capability checks occur only after WordPress has loaded the pluggable functions.

```php
register_rest_route('artpulse/v1', '/conversations', [
    'methods'  => 'GET',
    'callback' => 'ap_get_conversations',
    'permission_callback' => function () {
        return current_user_can('read');
    }
]);
```

## 3. Testing for Early Calls

Enable debugging and visit the admin dashboard. Check `wp-content/debug.log` for fatal errors or warnings.

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 4. Quick Checklist

| Action                                      | Safe? |
|---------------------------------------------|------|
| `current_user_can()` inside `init`          | ✅   |
| `wp_get_current_user()` inside `admin_init` | ✅   |
| Direct usage in plugin global scope         | ❌   |
| REST `permission_callback` returning a bool | ✅   |

