---
title: Community Spaces & Event Threads Codex
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Community Spaces & Event Threads Codex

This document describes how ArtPulse facilitates community forums and event-based discussion threads. The feature provides persistent forums and per-event comment threads to boost engagement among members, artists and organizations.

## Overview

| Feature | Purpose |
|---------|---------|
| Forums | Ongoing discussions organized by category |
| Event Threads | Auto-generated spaces linked to specific events |
| Comment Module | Reusable component for artworks, posts and threads |

### Roles & Permissions

| Role | Can Post | Can Moderate | Create Forums |
|------|---------|--------------|---------------|
| Member | ✅ | ❌ | ❌ |
| Artist | ✅ | Limited | ❌ |
| Organization | ✅ | ✅ (their events) | ❌ |
| Admin/Mod | ✅ | ✅ | ✅ |

## Database Tables

Several custom tables store forum data while leveraging WordPress posts for threads.

```sql
CREATE TABLE IF NOT EXISTS wp_ap_forums (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  slug VARCHAR(200) NOT NULL,
  visibility VARCHAR(20) DEFAULT 'public',
  created_by BIGINT NOT NULL
);

CREATE TABLE IF NOT EXISTS wp_ap_topics (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  forum_id BIGINT NOT NULL,
  title VARCHAR(200) NOT NULL,
  user_id BIGINT NOT NULL,
  created_at DATETIME NOT NULL,
  pinned TINYINT DEFAULT 0,
  locked TINYINT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS wp_ap_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  topic_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  parent_post_id BIGINT NULL,
  content LONGTEXT NOT NULL,
  created_at DATETIME NOT NULL,
  edited_at DATETIME NULL,
  is_deleted TINYINT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS wp_ap_event_threads (
  event_id BIGINT PRIMARY KEY,
  topic_id BIGINT NOT NULL
);
```

## REST API Endpoints

The REST layer exposes forum operations:

```
GET  /forums
GET  /forums/{id}/topics
POST /forums/{id}/topics
POST /topics/{id}/posts
GET  /events/{id}/thread      # auto-create if missing
POST /posts/{id}/react        # likes and emoji reactions
```

Requests must include a valid nonce. Moderation actions require the `moderate_forums` capability.

## Front-End Components

- **EventDiscussionWidget** – renders the event thread and optionally polls for new replies.
- **ForumBrowserPage** – browse categories and recent topics.
- **TopicViewComponent** – shows original post with replies and pinned notices.
- **CommentThreadComponent** – used by artworks or blog posts for threaded comments.
- **PostEditorComponent** – Markdown or rich text input with media attachments.

## Usage Notes

An event thread is automatically created the first time a user opens the “Join the Discussion” button on an event page. The event host can pin announcements or moderate replies. Public forum categories support general chat, AMAs and feedback. Organizations may embed the **EventDiscussionWidget** in promotional pages so members can coordinate before, during and after the event.

## Optional Enhancements

- Notify users when their posts receive replies or mentions.
- Add moderation tools to flag or temporarily hide inappropriate content.
- Reward active posters with badges for participation.

> 💬 *Found something outdated? [Submit Feedback](../../feedback.md)*
