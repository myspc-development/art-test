---
title: Event Template & Duplication Tools
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Event Template & Duplication Tools

This codex explains the optional module used for cloning events or saving them as reusable templates.
It helps organizations quickly create new events without repeating the same setup each time.

## 1. Duplicate Event Functionality

A "Duplicate" action creates a new draft event based on an existing one. All event
fields, ticket types and images are copied, while RSVPs and comments are omitted.
The new post title is suffixed with `(Copy)` and the user is redirected to the
edit screen.

PHP example:

```php
use ArtPulse\Core\EventTemplateManager;
$new_id = EventTemplateManager::duplicate_event( $event_id );
wp_redirect( get_edit_post_link( $new_id, 'raw' ) );
```

## 2. Save & Use Event Templates

Any event can be saved as a template. Templates are stored as a custom post type
`ap_event_template` with its meta in the `_ap_template_data` field. When starting
a new event the organization may choose one of its templates and all fields will
be pre-filled.

```php
$template_id = EventTemplateManager::save_as_template( $event_id );
$event_id     = EventTemplateManager::create_from_template( $template_id );
```

## 3. Permissions & Logging

Only users capable of managing events may create templates or duplicate events.
Actions should be logged with the event or template ID and user ID so plugins can
audit usage via hooks.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
