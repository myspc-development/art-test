# Divorce Organizer Codex: Step 3 – Audit Logging & Activity Tracking

This guide outlines how to record system and user actions for compliance and administrative review.

## Goals
- Capture a timestamp, user ID, action type and description for each important action.
- Store entries in `wp_do_audit_log`.
- Provide a read‑only REST API for administrators.
- (Optional) Display logs in a dashboard panel.

## Components
- `class-audit-logger.php` – logging service.
- `wp_do_audit_log` database table.
- API controller at `do/v1/audit-log`.
- React `AuditLogPanel` (optional).

## 1. Create the Logging Service
Place `class-audit-logger.php` in the `includes` directory.

```php
class DO_Audit_Logger {
    public static function log($action_type, $description, $meta = []) {
        global $wpdb;

        $wpdb->insert("{$wpdb->prefix}do_audit_log", [
            'user_id'     => get_current_user_id(),
            'action_type' => sanitize_text_field($action_type),
            'description' => sanitize_textarea_field($description),
            'meta_json'   => wp_json_encode($meta),
            'created_at'  => current_time('mysql'),
        ]);
    }

    public static function get_logs($limit = 100, $offset = 0) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}do_audit_log ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ), ARRAY_A);
    }
}
```

## 2. Create the Table on Activation
Add this to the plugin's `activate()` method in `includes/Plugin.php`:

```php
global $wpdb;

$table_name = $wpdb->prefix . 'do_audit_log';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT(20) UNSIGNED,
    action_type VARCHAR(100),
    description TEXT,
    meta_json LONGTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) $charset_collate;";

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
dbDelta($sql);
```

## 3. Hook Into Key Actions
Log notable events, for example after a document upload:

```php
DO_Audit_Logger::log(
    'document_upload',
    'User uploaded a document: ' . $file_name,
    ['file_id' => $doc_id, 'case' => $case_id]
);
```

Other hooks include timeline edits, settings changes, AI analysis triggers, notification preferences and (optionally) login/logout.

## 4. Read‑Only REST API
Create `class-audit-controller.php` under `includes/api`:

```php
class DO_Audit_Controller extends WP_REST_Controller {
    public function register_routes() {
        register_rest_route('do/v1', '/audit-log', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_logs'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            }
        ]);
    }

    public function get_logs($request) {
        $limit  = $request->get_param('limit') ?: 100;
        $offset = $request->get_param('offset') ?: 0;
        return DO_Audit_Logger::get_logs($limit, $offset);
    }
}
```

Register the controller in `divorce-organizer.php`:

```php
require_once DO_PLUGIN_DIR . 'includes/api/class-audit-controller.php';
$controller = new DO_Audit_Controller();
$controller->register_routes();
```

## 5. Optional React Log Viewer
`AuditLogPanel.tsx` fetches logs from the REST endpoint and renders a table:

```tsx
import { useEffect, useState } from 'react';

export default function AuditLogPanel() {
  const [logs, setLogs] = useState([]);

  useEffect(() => {
    fetch('/wp-json/do/v1/audit-log?limit=50', { credentials: 'include' })
      .then(res => res.json())
      .then(setLogs);
  }, []);

  return (
    <div className="space-y-4">
      <h2 className="text-xl font-bold">Audit Log</h2>
      <table className="w-full text-sm border">
        <thead>
          <tr><th>Time</th><th>User</th><th>Action</th><th>Description</th></tr>
        </thead>
        <tbody>
          {logs.map((log, i) => (
            <tr key={i}>
              <td>{log.created_at}</td>
              <td>{log.user_id}</td>
              <td>{log.action_type}</td>
              <td>{log.description}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

## 6. Testing Checklist
- Document upload generates a log entry with file ID.
- Timeline edit records a log with event ID.
- AI analysis actions appear in the log.
- Only admins can retrieve logs via REST.

## Tips
- Never store passwords or sensitive file contents.
- Use `wp_json_encode()` for metadata arrays.
- Add indexes if the log table grows large.
