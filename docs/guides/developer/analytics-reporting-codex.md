---
title: ArtPulse Codex: Analytics & Reporting
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Analytics & Reporting

This guide consolidates instructions for tracking engagement and generating reports for administrators and organizations. It also covers per-user insights available to artists or individual users. Each section is optional and focuses on how to enable or extend analytics modules.

## 1. Data Tracking

Track user interactions such as event views, RSVPs, favorites and ticket purchases. Store summary counts in the `ap_event_stats` table and keep raw details like IP, user agent and timestamp in `ap_event_logs` for deeper analysis.

- Record the action type (`view`, `rsvp`, `favorite`, `purchase`).
- Include `event_id`, `user_id` when logged in, and the session or visitor ID.
- Log the source (web, mobile, email link) to understand which channels drive activity.

## 2. Backend Logging

Use the `LoggingManager` class to handle inserts into `ap_event_logs` and to rotate tables once a month. Hooks allow other plugins to tap into the logging process or filter out events.

```php
LoggingManager::log('rsvp', $event_id, $user_id, [ 'source' => 'calendar' ]);
```

## 3. REST Endpoints

Expose aggregated statistics via the `/analytics` namespace. Endpoints respect user capabilities and sanitize all parameters.

- `GET /wp-json/artpulse/v1/analytics/event/{id}` – summary counts for an event.
- `GET /wp-json/artpulse/v1/analytics/trends?event_id=1&days=30` – time series data.
- `GET /wp-json/artpulse/v1/analytics/user/{id}` – individual engagement history.
- `GET /wp-json/artpulse/v1/analytics/community/messaging` – message volume and top users.
- `GET /wp-json/artpulse/v1/analytics/community/comments` – comment counts and flagged totals.
- `GET /wp-json/artpulse/v1/analytics/community/forums` – forum thread and reply metrics.

Results can be cached using transients. When exporting, stream CSV data with a `fputcsv()` loop to avoid memory issues.

## 4. Dashboard Widgets

Add overview widgets to the WordPress admin or organization dashboard. Use Chart.js or the built‑in `wp.data` store to render charts.

- Event statistics bar chart (views, RSVPs, purchases).
- Top referrers showing which sites or campaigns drive traffic.
- Recent user activity list with profile links.

Widgets register through `artpulse_register_dashboard_widget` so themes or child plugins can extend the dashboard.

## 5. Per-User Insights

Artists and individual users can view their own engagement metrics in a personal dashboard.

- Show total events created, average RSVPs and favorites.
- Provide a table of the user's most successful events with links.
- Allow exporting personal analytics as CSV.

These endpoints use `current_user_can( 'read' )` checks and only expose data relevant to the logged-in user.

## 6. Security Considerations

- Escape all output and sanitize input for analytics endpoints.
- Use capability checks (`manage_options` or a custom role) before showing admin widgets or exporting data.
- Ensure IP addresses or potentially sensitive logs are not displayed to regular users.
- When storing personal data, comply with GDPR and provide a way to remove logs on request.

## 7. Documentation & Extensibility

Document any new REST routes in the API Reference and update the README. Developers can hook into `artpulse_analytics_process_log` to transform events before they are stored and add new widgets with `artpulse_register_dashboard_widget`.


## 8. 🔧 REST API Integration for Organization Role Matrix

The Organization Role Matrix tab in the admin uses a React interface to load role data from custom REST endpoints. When localizing script data for this component, pass a relative `api_path` so `apiFetch()` can correctly prefix it with `wp-json/`.

```php
wp_localize_script( 'ap-org-roles', 'ArtPulseOrgRoles', [
    'api_path' => 'artpulse/v1/org-roles',
    'nonce'    => wp_create_nonce( 'wp_rest' ),
] );
```

Fetch the endpoints in JavaScript using the `path` property:

```js
apiFetch( { path: ArtPulseOrgRoles.api_path } );
apiFetch( { path: ArtPulseOrgRoles.api_path + '/users' } );
```

If you prefer to localize the full URL, use the `url` option so WordPress does not prepend `wp-json/` twice:

```php
'api_url' => rest_url( 'artpulse/v1/org-roles' )
```

```js
apiFetch( { url: ArtPulseOrgRoles.api_url } );
apiFetch( { url: ArtPulseOrgRoles.api_url + '/users' } );
```

Validate the routes with `wp rest route list | grep org-roles` and ensure a 200 response:

```php
public function test_org_roles_route() {
    $response = rest_do_request( '/artpulse/v1/org-roles' );
    $this->assertSame( 200, $response->get_status() );
}
```

Example requests:

```
GET /wp-json/artpulse/v1/org-roles
GET /wp-json/artpulse/v1/org-roles/users
GET /wp-json/artpulse/v1/org-roles/export.csv
```

The CSV export returns a table of users and their roles:

```
Name,Email,Roles
Sarah O’Neill,sarah@org.com,"curator,artist"
```

When `dompdf` is available, `/wp-json/artpulse/v1/org-roles/export.pdf` streams
the same dataset as a PDF file.

### Snapshot Builder

Scheduled exports call `SnapshotBuilder::generate_csv()` or `generate_pdf()`
when weekly or monthly cron jobs run. Administrators can trigger the same logic
manually from the **Snapshots** page under **ArtPulse → Settings**.
