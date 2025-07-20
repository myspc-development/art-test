---
title: ArtPulse Codex: Moderation & Flagging
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Moderation & Flagging

This guide describes how to implement the optional moderation system for handling user reports and abusive content. It outlines the database schema, REST endpoints and admin workflows to maintain community trust.

## 1. Feature Scope

- **Flagging** – Users can report inappropriate or spammy content.
- **Admin Review** – Administrators can dismiss or remove flagged items.
- **Reason Logging** – Store the reason and optional notes from the reporter.
- **Notifications** – Send email or dashboard alerts when new flags are created.

## 2. Database Schema

Create an `ap_flags` table with the following columns:

| Column | Type | Description |
|-------|------|-------------|
| `id` | int | Auto-increment primary key |
| `content_id` | int | ID of the flagged post, comment or profile |
| `content_type` | string | `event`, `comment`, `qa_thread`, etc. |
| `reason` | string | `spam`, `offensive`, `irrelevant`, `other` |
| `notes` | text | Optional user input |
| `reporter_id` | int | ID of the user who flagged it |
| `status` | string | `open`, `dismissed`, `removed` |
| `timestamp` | datetime | Created at |

## 3. REST API Endpoints

Register endpoints under `/artpulse/v1`:

- `POST /flag` – Create a flag. Requires auth and blocks duplicate reports by the same user on the same item.
- `GET /flags` – List flags with optional `status` and `content_type` filters. Requires `manage_options` capability.
- `POST /flags/resolve` – Update a flag's status. Optionally delete the flagged content and notify the original poster.

## 4. Frontend Integration

Show a **Flag this** action next to events, comments and profiles. A modal collects the reason and optional notes before posting to `/flag`.

Add a new admin screen **Content Flags** that lists reports with filters for content type, status and report count. Quick actions allow dismissing or removing an item.

## 5. Permissions & Rate Limits

Logged-in users may submit up to 10 flags per day. Apply a `ap_flag_submission_allowed` filter to enforce the limit:

```php
add_filter( 'ap_flag_submission_allowed', function ( $user_id ) {
    $count = ap_get_flag_count_today( $user_id );
    return $count < 10;
});
```

Administrators can view and resolve flags. All updates should include a nonce for security.

## 6. Notification System

Optionally send an email or dashboard notification to admins when a new flag is created. Slack webhooks can also be configured for moderation alerts.

## 7. QA Checklist

- User flags content → Entry created in `ap_flags` with `status = open`.
- User attempts to flag again → Request denied.
- Admin marks "removed" → Content deleted and original poster emailed.
- Admin marks "dismissed" → Flag closed with no content change.

## 8. Developer Checklist

- Table `ap_flags` exists and records submissions.
- REST endpoints register on `rest_api_init`.
- Validate content IDs before accepting a flag.
- Admin UI is paginated and protected with a nonce.
- Duplicate flags are blocked either in the database or logic layer.

## 9. Future Extensions

- Auto-hide content flagged more than five times.
- Block abusive flaggers to prevent trolling.
- Use machine-learning signals to help triage reports.
