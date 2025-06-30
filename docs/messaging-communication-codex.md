# ArtPulse Codex: Messaging & Communication

This guide covers how artists communicate with attendees. It explains the direct messaging endpoints, reminder scheduling and comment moderation tools.

## 1. Direct Messaging

Artists can send a message to every RSVP or a specific attendee. A dashboard form collects `subject` and `message` then posts to:

```
POST /wp-json/artpulse/v1/event/{id}/email-rsvps
POST /wp-json/artpulse/v1/event/{id}/attendees/{user_id}/message
```

Handlers verify the organizer's permission. If `user_id` is omitted, the message goes to all RSVP'd users. Emails are sent with `wp_mail()` and can also be logged through `NotificationManager`.

## 2. Automated Reminders

`ReminderManager` lets artists schedule reminder emails for an event. Reminders are stored in the `ap_event_reminders` option and queued with `wp_schedule_single_event`. Typical choices include "24 hours before" or a custom date. When the `artpulse_send_reminder` cron action fires, all RSVP'd users are emailed the reminder text.

## 3. Public Comments Management

Use the endpoints described in [Event Comments Codex](event-comments-codex.md) to list, reply to and moderate comments. The dashboard shows recent comments with attendee name, avatar and timestamp. Organizers may reply, delete or mark comments resolved. Deletion is limited to the event owner.

Accessibility tips: add ARIA labels to modal forms and display a confirmation notice after sending messages or scheduling a reminder.

