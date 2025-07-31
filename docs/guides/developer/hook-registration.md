---
title: Registerable Trait Usage
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Registerable Trait Usage

Many plugin classes only need to attach WordPress hooks on load. To reduce boilerplate these classes can use the `Registerable` trait.

```php
use ArtPulse\Traits\Registerable;

class ExampleController {
    use Registerable;

    private const HOOKS = [
        'rest_api_init' => 'register_routes',
        'init'         => 'maybe_install_table',
    ];

    public static function register_routes() { /* ... */ }
    public static function maybe_install_table() { /* ... */ }
}
```

Calling `ExampleController::register()` attaches the defined actions (and filters when `type => 'filter'` is provided). Constants or a static `$hooks` property are supported.
