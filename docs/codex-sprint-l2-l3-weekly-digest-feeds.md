---
title: Codex: Sprint L2 + L3 – Weekly Digests & Syndication Feeds
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Codex: Sprint L2 + L3 – Weekly Digests & Syndication Feeds

## Overview
These sprints introduce the Audience Re‑engagement & Distribution Engine. Weekly email digests keep users returning while open feeds let partners pull events into their own calendars and sites.

## Goals
| Tool | Purpose |
| --- | --- |
| **Weekly Digest Email** | Re‑engage users via personalized, timely event picks |
| **Syndication Feeds** | Enable external calendars, CMS and apps to pull events |
| **Preferences Panel** | Let users control digest frequency and topics |
| **Email & Feed Metrics** | Visibility for partners and internal strategy |

## Sprint L2 – Personalized Weekly Digest
### A. Digest Logic
- One email per opted‑in user, sent weekly (Sunday 7 AM default)
- Content is selected from followed orgs, saved tags and past interactions

### B. Digest Email Format
- Header: **"Your Week in Art"**
- **Featured Picks (3)** – relevance and freshness
- **Quick Hits (5)** – inline links by region or tag
- Followed org highlights
- Optional *From the Community* thread
- Footer links to unsubscribe or edit preferences

### C. Digest Generator
Scheduled via `wp_cron`:
```php
wp_schedule_event( strtotime('Sunday 7am'), 'weekly', 'ap_send_digests' );
```
Per user:
```php
ap_generate_digest_for_user($user_id);
wp_mail($user_email, $subject, $html_content);
```

### D. Preferences & Opt‑Out
Route: `GET /profile/preferences`
Users can toggle the digest, pick topics, choose weekly or monthly cadence and preview the next email.

### E. Admin Metrics
Track open and click rates, reach per org or tag and unsubscribe counts.

### F. Weekly Recommendations Job
`WeeklyRecommendations::register()` boots the recommendation engine. It hooks
into `init` to schedule a weekly cron task:
```php
ArtPulse\Personalization\WeeklyRecommendations::register();
wp_schedule_event( time(), 'weekly', 'ap_generate_recommendations' );
```
When the `ap_generate_recommendations` action runs, `generate()` loops through
all users, fetches IDs from `RecommendationEngine::get_recommendations()` and
saves them to the `ap_weekly_recommendations` user meta. The digest and
`[ap_recommendations]` shortcode read from this cache.

## Sprint L3 – Syndication Feeds (iCal, JSON, RSS)
### A. Feed Types
| Type | Route |
| --- | --- |
| iCal | `/feeds/events.ics` |
| JSON | `/feeds/events.json` |
| RSS | `/feeds/events.xml` |
All support filters like `?org=12&tag=sculpture&region=brooklyn`.

### B. Use Cases
| Audience | Feed Use |
| --- | --- |
| Public | Add ArtPulse events to Google Calendar |
| Bloggers | Auto‑show weekly highlights |
| Partner Orgs | Sync to internal CMS or civic calendar |
| Journalists | Get upcoming shows by topic or curator |

### C. Feed Format Sample
**iCal**
```ical
BEGIN:VEVENT
SUMMARY:Soundscape Ritual
DTSTART;TZID=America/New_York:20250819T190000
URL:https://artpulse.io/event/103
LOCATION:Red Hook Pier
END:VEVENT
```
**JSON**
```json
{
  "event_id": 103,
  "title": "Soundscape Ritual",
  "start": "2025-08-19T19:00:00Z",
  "tags": ["performance", "sound"],
  "org": "Bridge Arts"
}
```

### D. Filtered Feed Links

Use `ap_filtered_feed_link()` to build newsletter or CMS feeds with filters.

```php
$link = ArtPulse\Frontend\ap_filtered_feed_link(['org' => 44, 'tag' => 'print']);
```

This returns a URL such as `https://example.com/feeds/events.ics?org=44&tag=print`.

## Shared Features & UI
| Feature | Location |
| --- | --- |
| User Preferences Panel | `/profile/preferences` |
| Partner Feed Builder | `/tools/feed-builder` |
| Digest Previewer | `/admin/emails?user_id=XX` |
| Feed Docs Page | `/docs/feeds` |

## Access Control
| Feature | Role |
| --- | --- |
| Digest subscription | Public users |
| Feed URLs | Public (cached) |
| Admin previews & metrics | Staff, Partner‑tier orgs |

### Implementation Notes
- Feed responses are cached for 15 minutes via WordPress transients to reduce load.
- Each feed request records an entry in `ap_feed_logs` for analytics charts.

## Codex Docs to Update
- `email-digest.md`
- `digest-preferences.md`
- `feeds-reference.md`
- `partner-syndication-guide.md`

## Developer Sprint Checklist
- [x] Digest generator cron + template
- [x] Preferences panel live
- [x] iCal/JSON/RSS endpoints
- [x] Feed filter handling + caching
- [x] Analytics logs + charts

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
