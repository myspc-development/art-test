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

## Codex Docs to Update
- `email-digest.md`
- `digest-preferences.md`
- `feeds-reference.md`
- `partner-syndication-guide.md`

## Developer Sprint Checklist
- [ ] Digest generator cron + template
- [ ] Preferences panel live
- [ ] iCal/JSON/RSS endpoints
- [ ] Feed filter handling + caching
- [ ] Analytics logs + charts
