---
title: Detect Duplicate REST Route Registrations
category: diagnostics, REST API, development
audience: developer
status: active
related: rest-api.md, plugin-architecture.md
last_updated: 2025-07-28
---

# 🚨 Diagnostic Prompt: Prevent Duplicate REST Route Definitions

Use this guide to add an automated check to your development workflow to detect and report duplicate REST API route registrations during plugin development.

---

## 🎯 Purpose

Duplicate `register_rest_route()` calls can lead to:

- Endpoint collisions
- Conflicting handlers
- Hard-to-debug API errors
- Performance degradation

---

## 🧰 Implementation Options

### Option 1: Hook-Based Warning System (Development Only)

Add the following to a file loaded during development (e.g., `includes/dev/debug-rest.php`):

```php
add_filter('rest_endpoints', function ($endpoints) {
    $seen = [];
    foreach ($endpoints as $route => $handlers) {
        if (isset($seen[$route])) {
            error_log("[REST DUPLICATE] Route already registered: $route");
        }
        $seen[$route] = true;
    }
    return $endpoints;
});
```
