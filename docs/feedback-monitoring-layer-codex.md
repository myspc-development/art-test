---
title: ArtPulse Codex: Feedback & Monitoring Layer
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Feedback & Monitoring Layer

This module gathers user feedback, captures bugs and keeps the platform healthy. It
also surfaces build metadata for QA.

## Goals
- Collect user feedback in context
- Track bugs tied to specific sessions
- Monitor REST and cron health
- Provide release snapshots for QA

## 1. In-App Feedback Widget
The `FeedbackButton.jsx` component shows on all dashboards. Clicking the button
opens a modal where users can select **I like this feature**, **Something is broken**
or **I'm confused**.

Submitted feedback posts JSON to `POST /artpulse/v1/feedback/submit` and stores
records in the `ap_feedback` table:

```sql
CREATE TABLE `ap_feedback` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT,
  `page` VARCHAR(255),
  `type` VARCHAR(50),
  `message` TEXT,
  `submitted_at` DATETIME
);
```

Screenshots may be attached via a `screenshot` field if uploads are enabled.

## 2. Bug Reporter
Authenticated users can press **Ctrl+Shift+B** to open a bug modal. Fields are
pre-filled with the current page, user agent and any console errors. Public users
may be directed to a form or GitHub issue template.

## 3. System Monitoring
### REST Latency Logging
```php
add_action('rest_post_dispatch', function ($result, $server, $request) {
  $start = microtime(true); // track in middleware
  $duration = microtime(true) - $start;

  if ($duration > 1.5) {
    $route = $request->get_route();
    error_log("[REST SLOW] {$route} took {$duration}s");
  }
});
```

### Cron Watchdog
```php
$last_run = get_option('ap_last_cron_run_rankings');
if (time() - $last_run > 7200) {
  error_log('[CRON] Rankings cron has not run in 2h!');
}
```

### JS Console Tracking (optional)
```js
window.onerror = function(msg, url, lineNo, columnNo, error) {
  fetch('/wp-json/artpulse/v1/errors', {
    method: 'POST',
    body: JSON.stringify({msg, url, lineNo, columnNo})
  });
};
```

## 4. QA Snapshot & Version Metadata
Display version and commit hash in the admin footer:

```php
echo '<p style="opacity:0.4">Version: ' . ART_PULSE_VERSION .
' • Build: ' . substr(GIT_COMMIT_HASH, 0, 7) . '</p>';
```

An optional `GET /artpulse/v1/status` route can return build info and metrics.

## Developer Checklist
- `FeedbackButton.jsx` mounted on dashboards
- `/feedback/submit` endpoint active
- `ap_feedback` table created
- REST latency logging running
- Cron watchdog executing hourly
- QA footer metadata displayed

💬 Found something outdated? Submit Feedback
