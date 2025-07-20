---
title: Growth Stack Codex
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Growth Stack Codex

Toolkit for Amplifying Reach, Retention & Distribution on ArtPulse.

This overview bundles together Sprints L1–L3 into a partner ready deployment layer.

## Purpose

The Growth Stack enables partners, artists and organizations to:

- **Attract** audiences to their own sites with embeddable event widgets.
- **Retain** users through smart, personalized weekly digests.
- **Distribute** events to apps, blogs and calendars via open feeds.

Combined, these modules support sustained audience growth and visibility across platforms.

## 🧪 Modules

| Level | Tool                 | User                     |
|-------|----------------------|--------------------------|
| L1    | Embeddable Widgets   | Public websites, partners|
| L2    | Personalized Digests | Logged‑in users          |
| L3    | Syndication Feeds    | Blogs, civic calendars, apps|

### L1: Embeddable Widgets

Allow anyone to embed a filtered list of events on their website.

- Filter by tag, region or organization.
- Choose card or list layout, with light or dark themes.
- Auto‑updating content served via iframe or script tag.
- Click and impression analytics.

Example iframe:

```html
<iframe src="https://artpulse.io/embed?org=12&layout=cards&theme=dark"></iframe>
```

Example script tag:

```html
<script src="https://artpulse.io/embed.js" data-org="12" data-tag="sculpture" data-theme="light"></script>
```

Preview and generate embeds at `/tools/embed`.

### L2: Personalized Weekly Digests

Automatically tailored weekly emails based on each user’s interest graph.

- Top 3 suggested shows and quick‑hit upcoming list.
- Highlights from followed organizations.
- Optional “From the community” feature.
- Sent Sundays at 7 am by default (user can adjust in profile preferences).

Digest logic uses RSVPs, saved tags and followed orgs to surface the most relevant events. Partners benefit from repeat views and automatic promotion of their programming without manual campaigns.

Metrics include open rate, click-through, unsubscribe rate and per-organization impact charts (opt-in).

### L3: Open Syndication Feeds

Expose ArtPulse events for consumption in other platforms.

| Format | URL                        |
|--------|---------------------------|
| iCal   | `/feeds/events.ics`       |
| JSON   | `/feeds/events.json`      |
| RSS    | `/feeds/events.xml`       |

Feeds support filter parameters, for example:

```
/feeds/events.json?org=44&tag=performance&region=oakland
```

Use cases include syncing to Google Calendar, auto‑publishing on civic sites or feeding data into Notion, Airtable or Zapier.

## 🔐 Permissions & Access

| Tool           | Access Level                      |
|----------------|-----------------------------------|
| Widget embed   | Public                            |
| Digest emails  | Logged‑in users (opt‑in)         |
| Feed URLs      | Public (rate limited)             |
| Partner metrics dashboard | Org admin or partner tier |

## 📊 Growth Stack Metrics

| Metric               | Example                                             |
|----------------------|-----------------------------------------------------|
| Widget Impressions   | 4,320 weekly views on six partner sites             |
| Digest Click‑through| Avg 21.8% across all users                           |
| Feed Subscribers     | 14 active iCal subscribers, three JSON apps         |
| Top Clicked Event    | “Queer Print Fair” (38%)                           |

## Codex Files

- `embed-widgets.md` – widget install and styling guide.
- `email-digests.md` – digest logic and partner tips.
- `feeds-reference.md` – API‑style documentation for syndication.
- `growth-stack-overview.md` – this document.
- `partner-metrics-guide.md` – interpreting email, feed and widget performance.

## ✅ Activation Checklist for New Partners

- [x] Add widget to homepage or “Events” tab.
- [x] Encourage users to follow the organization (activates digest).
- [x] Create a filtered feed (e.g., iCal link for newsletters).
- [x] Join analytics pilot (dashboard access).

## Outcomes

- Organic distribution means events show up across the web.
- Retained audiences lead to more revisits and attendance.
- Platform stickiness increases follows, RSVPs and shares.
- Partners gain independence with minimal setup per show.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
