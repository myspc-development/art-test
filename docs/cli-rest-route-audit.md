# REST Route Audit CLI

The `ap:audit-rest-routes` WP-CLI command scans registered REST API routes and reports conflicts where the same route and method map to different callbacks.

## Usage

```bash
wp ap:audit-rest-routes [--json] [--fix]
```

### OPTIONS

`--json`
: Return the conflicts in JSON format.

`--fix`
: Unregister duplicate callbacks, keeping only the first handler for each conflicting route and method.

## Examples

List conflicts:

```bash
wp ap:audit-rest-routes
```

Return conflicts as JSON:

```bash
wp ap:audit-rest-routes --json
```

Attempt to automatically remove duplicate callbacks:

```bash
wp ap:audit-rest-routes --fix
```

### Sample output

```
Route: /wp/v2/widgets
  Method: GET
    Plugin\\Callback::list - my-plugin (preferred)
    My\\Other_Callback::list - other-plugin
      Suggest: conditionally register using is_plugin_active('other-plugin')
```

```bash
$ wp ap:audit-rest-routes --json
{
    "/wp/v2/widgets": {
        "GET": [
            {
                "callback": "Plugin\\\\Callback::list",
                "plugin": "my-plugin"
            },
            {
                "callback": "My\\\\Other_Callback::list",
                "plugin": "other-plugin"
            }
        ]
    }
}
```

```bash
$ wp ap:audit-rest-routes --fix
Success: Conflicting routes cleaned.
```

