# ArtPulse Codex: Messaging & Communication

This guide covers how artists communicate with attendees. It explains the direct messaging endpoints, reminder scheduling, announcement scheduling and comment moderation tools. Recent updates add a unified inbox with read tracking.

## 1. Direct Messaging

Artists can send a message to every RSVP or a specific attendee. A dashboard form collects `subject` and `message` then posts to:

```
POST /wp-json/artpulse/v1/event/{id}/email-rsvps
POST /wp-json/artpulse/v1/event/{id}/attendees/{user_id}/message
```

Handlers verify the organizer's permission. If `user_id` is omitted, the message goes to all RSVP'd users. Emails are sent with `EmailNotifier::send()` and can also be logged through `NotificationManager`.

## 2. Automated Reminders

`ReminderManager` lets artists schedule reminder emails for an event. Reminders are stored in the `ap_event_reminders` option and queued with `wp_schedule_single_event`. Typical choices include "24 hours before" or a custom date. When the `artpulse_send_reminder` cron action fires, all RSVP'd users are emailed the reminder text.

## 3. Scheduled Announcements

`ScheduledMessageManager` creates the `ap_scheduled_messages` table. Messages are queued with the `ap_process_scheduled_messages` cron event and delivered to each follower of the organization using `OrgCommunicationsCenter::insert_message()`. Each row stores the subject, body, channels to send (email, SMS or in‑app) and optional recipient segments.

Example call:

```php
ScheduledMessageManager::schedule_message($org_id, $user_id, 'Show update', 'Doors open at 9pm', strtotime('+1 day'));
```

## 4. Segmented Recipients

Segments are currently limited to follower lists stored on the organization post. The `get_recipients()` method can be expanded to target VIP ticket holders or team members. Pass segment slugs when scheduling to filter the audience.

## 5. Two‑Way Inbox

`OrgCommunicationsCenter` manages the `ap_org_messages` table. Messages are grouped by `thread_id` for threaded conversations. `insert_message()` adds a row while `get_thread_messages()` retrieves the history for display in the dashboard inbox. Call `mark_read()` after a message is viewed.

## 6. Delivery & Read Tracking

Every entry in `ap_org_messages` has a `status` column. When an organizer views a message, update the row to `read`. Future integrations can update status via webhooks for email/SMS providers.

## 7. Message Logs

Message rows remain in the database for auditing. Admins can query the tables and export CSVs for a complete communication history.

## 8. Public Comments Management

Use the endpoints described in [Event Comments Codex](event-comments-codex.md) to list, reply to and moderate comments. The dashboard shows recent comments with attendee name, avatar and timestamp. Organizers may reply, delete or mark comments resolved. Deletion is limited to the event owner.

Accessibility tips: add ARIA labels to modal forms and display a confirmation notice after sending messages or scheduling a reminder.

## 9. User Messaging REST API

A dedicated `/messages` endpoint allows members to exchange direct messages.
Messages are stored in the `ap_messages` table:

| Column       | Type        | Notes                                |
|--------------|-------------|--------------------------------------|
| `id`         | BIGINT PK   | Auto increment primary key           |
| `sender_id`  | BIGINT      | ID of the user sending the message   |
| `recipient_id`| BIGINT     | ID of the user receiving the message |
| `content`    | TEXT        | Message body (sanitized HTML)        |
| `created_at` | DATETIME    | Timestamp when inserted              |
| `is_read`    | TINYINT(1)  | 0 = unread, 1 = read                 |

### Routes

```text
POST /wp-json/artpulse/v1/messages
GET  /wp-json/artpulse/v1/messages?with={user_id}
GET  /wp-json/artpulse/v1/conversations
POST /wp-json/artpulse/v1/message/read
```

`POST /messages` requires `recipient_id` and `content`. It saves the row
and emails the recipient. `GET /messages` returns the full thread between
the current user and another user identified by the `with` parameter.
`/conversations` lists user IDs that the current user has messaged.
`/message/read` marks specific message IDs as read.

### Examples

```bash
# Send a message
curl -X POST -H 'Content-Type: application/json' -u alice:pass \
  -d '{"recipient_id": 5, "content": "Hi!"}' \
  https://example.com/wp-json/artpulse/v1/messages

# Retrieve the thread
curl -H 'Content-Type: application/json' -u alice:pass \
  'https://example.com/wp-json/artpulse/v1/messages?with=5'
```
