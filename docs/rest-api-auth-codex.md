# ArtPulse Codex: REST API Authentication & Permissions

This guide explains how to secure custom REST API endpoints. It focuses on nonce validation and capability checks when registering routes.

## 1. Verifying Requests

Use a `permission_callback` that combines `current_user_can()` with a nonce check:

```php
register_rest_route('artpulse/v1', '/conversations', array(
    'methods'  => 'GET',
    'callback' => 'ap_get_conversations',
    'permission_callback' => function () {
        return current_user_can('read') &&
            check_ajax_referer('artpulse_nonce', '_wpnonce', false);
    }
));
```

`check_ajax_referer()` validates the nonce value sent with the request and prevents processing if it fails. Return `true` only when the user has the required capability **and** the nonce passes.

### Passing the Nonce to JavaScript

Localize your scripts to expose the nonce safely on the page:

```php
wp_enqueue_script('ap-admin');
wp_localize_script('ap-admin', 'apAuth', [
    'nonce'   => wp_create_nonce('artpulse_nonce'),
    'apiBase' => rest_url('artpulse/v1')
]);
```

In your JavaScript fetch calls, append the `_wpnonce` parameter from `apAuth`.

## 2. Structuring Routes

Group related endpoints under a versioned namespace such as `artpulse/v1`. Define CRUD callbacks separately and reuse the same `permission_callback` logic for consistency.
