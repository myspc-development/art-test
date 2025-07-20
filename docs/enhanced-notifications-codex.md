---
title: ArtPulse Codex: Enhanced Notifications
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Enhanced Notifications

This guide details the unified inbox REST routes, user notification preferences and the onboarding tour that walks new members through the dashboard. It complements the [Messaging & Communication Codex](messaging-communication-codex.md) and [UI/UX Polish Codex](ui-ux-polish-codex.md).

## 1. Unified Inbox Endpoints

The inbox aggregates messages, alerts and RSVP activity for the current user.

### `GET /wp-json/artpulse/v1/inbox`

Returns the most recent items. Example:

```bash
curl -H 'X-WP-Nonce: <nonce>' '/wp-json/artpulse/v1/inbox'
```

Each entry includes `id`, `type`, `content`, `timestamp` and `read` status. Types include `message`, `notification` and `rsvp`.

### `POST /wp-json/artpulse/v1/inbox/read`
Mark one or more items read. Provide `type` and either `id` or an array `ids[]`.

### `POST /wp-json/artpulse/v1/inbox/unread`
Reverts items to an unread state using the same parameters.

## 2. Notification Preferences

Users can update delivery methods via `POST /wp-json/artpulse/v1/user-preferences`.
Important parameters:

- `push_token` – device token for push notifications
- `phone_number` – number used for SMS alerts
- `sms_opt_in` – boolean flag for text messages
- `notification_prefs[email]` – enable email notifications
- `notification_prefs[push]` – enable push notifications
- `notification_prefs[sms]` – enable SMS notifications

Example request:

```bash
curl -X POST -H 'X-WP-Nonce: <nonce>' \
  -d 'notification_prefs[email]=1&notification_prefs[push]=1' \
  '/wp-json/artpulse/v1/user-preferences'
```

Settings are stored in user meta such as `ap_notification_prefs`.

## 3. Onboarding Tour

After registration, users see a brief tour highlighting key dashboard areas. Step completion is tracked in `ap_onboarding_steps` and the flag `ap_onboarding_completed`. Developers can modify the steps via the `artpulse_onboarding_steps` filter or provide custom templates in `templates/onboarding-*`.

💬 Found something outdated? Submit Feedback
