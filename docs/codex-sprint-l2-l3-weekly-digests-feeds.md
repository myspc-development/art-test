# Codex: Sprints L2 & L3 – Weekly Digests & Syndication Feeds

## Overview
These sprints introduce a re‑engagement engine combining personalized weekly digest emails with open syndication feeds. Users stay informed through curated event highlights while partners pull events into external calendars or CMS via iCal, JSON or RSS.

## Goals
- **Weekly Digest Email** – deliver one email per user with relevant events
- **Syndication Feeds** – publicly accessible iCal, JSON and RSS exports
- **Preferences Panel** – user control over topics and frequency
- **Email & Feed Metrics** – track engagement and reach

## L2 – Personalized Weekly Digest
### Digest Logic
- Opt‑in users receive an email every Sunday at 7 am
- Picks assembled from followed organizations, saved tags and past interactions
- One email per user regardless of number of followed orgs

### Email Format
1. **Header** – "Your Week in Art"
2. **Featured Picks (3)** – most relevant upcoming events
3. **Quick Hits (5)** – inline links grouped by region or tag
4. **Followed Orgs Highlights**
5. Optional **From the Community** section
6. Footer with unsubscribe and preference links

### Generator & Cron
```php
wp_schedule_event( strtotime('Sunday 7am'), 'weekly', 'ap_send_digests' );
```
Per user:
```php
ap_generate_digest_for_user( $user_id );
wp_mail( $user_email, $subject, $html_content );
```

### Preferences & Opt‑Out
Route: `GET /profile/preferences`
- Toggle digest on/off
- Select tags or topics of interest
- Choose weekly or monthly frequency
- Preview next email

### Admin Metrics
- Open and click rates
- Digest reach by org or tag
- Unsubscribe report

## L3 – Syndication Feeds
### Feed Types & Routes
| Type | Route |
|------|----------------|
| iCal | `/feeds/events.ics` |
| JSON | `/feeds/events.json` |
| RSS  | `/feeds/events.xml` |
All routes accept filters such as `?org=12&tag=sculpture&region=brooklyn`.

### Use Cases
- Public calendars or personal apps subscribe to event feeds
- Bloggers auto‑display weekly highlights
- Partner organizations sync events to their CMS
- Journalists pull upcoming shows by topic or curator

### Sample Output
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

## Shared UI & Access Control
- Preferences panel at `/profile/preferences`
- Feed builder at `/tools/feed-builder` for partners
- Digest previewer `/admin/emails?user_id=XX` (staff or partner tier)
- Feed docs at `/docs/feeds`
- Digest subscription and feed URLs are public; previews and metrics require staff or partner privileges

## Developer Sprint Checklist
- [ ] Digest generator cron + template
- [ ] Preferences panel live
- [ ] iCal/JSON/RSS endpoints
- [ ] Feed filter handling + caching
- [ ] Analytics logs + charts

## Codex Docs to Update
- `email-digest.md`
- `digest-preferences.md`
- `feeds-reference.md`
- `partner-syndication-guide.md`
