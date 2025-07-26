---
title: ArtPulse Codex: Webhook Automations
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Codex: Webhook Automations

This guide describes the optional webhook system for integrating ArtPulse events with external services. Organization administrators can register webhook URLs that will receive JSON payloads whenever selected events occur.

## Webhook Management

Webhooks are managed under the **Webhooks** tab on an organization dashboard. The tab loads the **Webhooks** widget which lists existing subscriptions and provides controls for editing or removing them. Select **Add Webhook** to open a form where you can enter an endpoint URL, choose the events that should trigger the webhook, and toggle whether the hook is active. Each webhook also has a unique secret used for payload signatures.

## Events

Supported events include:

- `ticket_sold` – fired when a visitor purchases a paid ticket
- `rsvp_created` – fired when an attendee RSVPs
- `payout_processed` – fired when a payout is completed

Additional events can be added via filters.

## Payload Format

Webhook payloads are sent as `application/json` and include a signature header:

```json
{
  "event": "ticket_sold",
  "timestamp": "2025-07-01T14:00:00Z",
  "data": { "ticket_id": 1 }
}
```

The request contains `X-ArtPulse-Signature` with an HMAC SHA-256 digest of the JSON body using the webhook secret.

## Retry Logic

Failed deliveries update the webhook status but retries are handled by WordPress cron. Administrators can monitor the last response code for each endpoint.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
