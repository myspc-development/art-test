# ArtPulse Codex: Event Ranking & Partner API

This guide outlines Sprint 3 features that add intelligence to event listings and expose a partner-ready API. Implementors should refer to it when building ranking computations or issuing API keys.

## 1. Event Ranking Algorithm

Events are ranked by quality and relevance. Scores combine recent views, saves, organizer verification and artist reputation. The computed ranking is stored per event in `ap_event_rankings` and updated hourly via cron.

### Rankings Table

```sql
CREATE TABLE `ap_event_rankings` (
  `event_id` BIGINT PRIMARY KEY,
  `score` DECIMAL(5,2),
  `updated_at` DATETIME
);
```

### Cron Job

```php
add_action('ap_cron_compute_rankings', 'ap_compute_event_scores');

function ap_compute_event_scores() {
  // Pull metrics, compute score per event, insert/update table
}
```

### REST Integration

`/artpulse/v1/events` accepts `orderby=rank` and falls back to post date when no score is present. Append `?show_score=1` to expose the score in JSON for debugging.

## 2. Open Partner API

Verified institutions can request API keys to embed event data. Keys are stored hashed in `ap_api_keys` and validated via a bearer token filter.

### API Keys Table

```sql
CREATE TABLE `ap_api_keys` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_hash` VARCHAR(255),
  `email` VARCHAR(255),
  `org_name` VARCHAR(255),
  `scopes` TEXT,
  `created_at` DATETIME
);
```

### Authentication Middleware

```php
add_filter('rest_pre_dispatch', function($result, $server, $request) {
  $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
  if (!str_starts_with($auth, 'Bearer ')) return $result;

  $token = substr($auth, 7);
  $hash  = hash('sha256', $token);

  $key = ap_get_api_key_by_hash($hash);
  if (!$key) return new WP_Error(401, 'Invalid API key');

  $request->set_param('partner_org', $key->org_name);
  return $result;
}, 10, 3);
```

### Available Endpoints

| Endpoint | Scope | Description |
| --- | --- | --- |
| `/api/v1/events` | `read:events` | Ranked event list |
| `/api/v1/events?region=brooklyn&tag=installation` | `read:events` | Filtered feed |
| `/api/v1/analytics/events` | `read:analytics` | Optional analytics summary |

### QA Checklist

- Valid API key returns JSON
- Requests without a key return 401
- Invalid keys also return 401
- Rate limit 10/sec per key

## Developer Sprint Checklist

- [x] `ap_event_rankings` created & scores update
- [x] REST `/events?orderby=rank` works
- [x] Admin UI for API keys live
- [ ] Bearer token auth & key hash check
- [ ] `/api/v1/events` documented

## Docs To Update

- Data Infrastructure → `ap_event_rankings`
- REST API → Partner API scopes
- Admin Features → API Key Manager
