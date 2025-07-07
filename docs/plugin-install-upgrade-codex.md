# Plugin Install & Upgrade Best Practices

This codex documents safe patterns for creating or updating database tables during plugin activation and upgrades. It complements the existing codex files under `docs/`.

## Validate Array Keys

When building column or index definitions in PHP arrays, always verify that the required keys exist before using them:

```php
foreach ($indexes as $index) {
    if (
        !isset($index['index_type']) ||
        !isset($index['index_name']) ||
        !isset($index['index_columns'])
    ) {
        error_log('Invalid index definition: ' . print_r($index, true));
        continue; // Skip invalid definitions
    }
    // Safe to build SQL
}
```

## Defensive Table/Column Creation

Never assume a schema array contains all expected fields. Check each key with `isset()` or provide a sensible default.

```php
if (isset($column['column_name']) && isset($column['column_type'])) {
    // build column definition
}
```

## Logging Invalid Schema

If a table, column or index definition is incomplete, log the problem and skip that part of the definition. This prevents malformed SQL from reaching `dbDelta()` and makes debugging easier.

## Avoid Empty Names in SQL

Double‑check that generated SQL statements never include empty table, column or index names. Statements such as `ADD `` ()` should never be produced.

## Activation Hooks

Activation hooks should abort gracefully if table creation fails. Use error logging to capture the failure, but avoid executing partially constructed SQL statements.

---

Following these practices helps prevent undefined index warnings and broken SQL during plugin installs or upgrades.
