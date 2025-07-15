# Codex: Sprint J1 – Webhook + Sync Framework

## Overview
This sprint enables external systems (CRMs, city portals, dashboards) to automatically receive updates from ArtPulse. Organizations can subscribe to events and receive real-time JSON payloads via secure webhooks.

## Goals
- **Webhook subscriptions** – partners or organizations subscribe to events like `event.created`.
- **Real-time payloads** – data is sent as JSON to partner endpoints.
- **Secure tokens** – each webhook includes a unique secret for HMAC validation.
- **Admin controls** – org admins manage their own webhook settings.

## Data Model
Create `ap_webhook_subscriptions`:

```sql
CREATE TABLE ap_webhook_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  org_id INT,
  event_type VARCHAR(50),
  target_url TEXT,
  secret_token VARCHAR(64),
  active BOOLEAN DEFAULT 1,
  last_sent DATETIME,
  retries INT DEFAULT 0
);
```

## Supported Event Types
| Event Type | Trigger |
|------------|---------|
| `event.created` | New event is published |
| `event.updated` | Event updated |
| `rsvp.created`  | New RSVP |
| `donation.received` | Donation logged |
| `user.followed` | A user follows an org or artist |

## Subscription UI
Org admin page: `/wp-admin?page=webhook-settings`
- Add new webhook
- Select event type(s)
- Enter target URL
- Auto-generate secret key
- Enable/disable toggle

## Signature & Security
Each request includes:

```http
POST /target-endpoint
Content-Type: application/json
X-Artpulse-Signature: sha256=<HMAC>
```

HMAC signature:

```php
hash_hmac('sha256', $payload, $secret_token);
```

Partners verify the signature before trusting the payload.

## Sending Webhooks
Helper example:

```php
function ap_trigger_webhooks($event_type, $data) {
  global $wpdb;
  $subs = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM ap_webhook_subscriptions WHERE event_type = %s AND active = 1",
    $event_type
  ));

  foreach ($subs as $sub) {
    $payload = json_encode($data);
    $sig = hash_hmac('sha256', $payload, $sub->secret_token);

    wp_remote_post($sub->target_url, [
      'headers' => ['X-Artpulse-Signature' => 'sha256=' . $sig],
      'body'    => $payload,
    ]);
  }
}
```

Payload example for `event.created`:

```json
{
  "event_id": 123,
  "title": "Community Sculpture Night",
  "start": "2025-08-12T18:00:00Z",
  "org_id": 44,
  "location": "Downtown Arts Plaza",
  "url": "https://artpulse.io/event/123"
}
```

## Retry & Error Handling
- Retry up to 3 times if a request returns `5xx` or times out.
- Mark failed attempts in `ap_webhook_logs`.
- Optional admin email alert after repeated failures.

## Webhook Logs
Table `ap_webhook_logs`:

| Column | Notes |
|-------|------|
| `id` | Auto |
| `subscription_id` | FK |
| `status_code` | 200 / 404 / 500 |
| `timestamp` | Sent time |
| `response_body` | Optional for debug |

Admin UI tab: “Delivery Logs”.

## QA Checklist
- Register webhook → test ping
- Event created → payload POSTed
- Signature valid on external tool
- Retry logic works on error
- Logs show response history

## Developer Sprint Checklist
- [ ] `ap_webhook_subscriptions` table live
- [ ] `ap_trigger_webhooks()` global
- [ ] Signature + HMAC verified
- [ ] Admin UI for org webhook settings
- [ ] Sample payloads documented
- [ ] Logs + retry engine built

## Codex Docs to Update
- Partner Integration – Webhook Setup, Signature Spec, Supported Events
- Org Admin Tools – Webhook Panel, Test Ping
- Developer Guide – Example syncing events to Airtable / Notion / Salesforce
- Security – HMAC Signatures, Token Rotation Guide
