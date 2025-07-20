---
title: Codex: Sprint L1 – Embeddable Event Widgets
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Codex: Sprint L1 – Embeddable Event Widgets

## Overview
This sprint introduces drop-in widgets so partners, galleries and institutions can showcase ArtPulse events on their own websites or blogs. By embedding a filtered feed or single highlight, organizations drive visibility and engagement outside the platform.

## Goals
- Customizable widget embed via script tag or iframe
- Style options for light/dark themes and card/grid layouts
- Filter support by tag, organization, date and region
- Analytics logging for impressions and clicks

## Embed Types & Entry Points
- **MiniFeed** – list of upcoming events
- **Highlight** – single featured event
- **Carousel** *(optional)* – scrolling cards for dynamic pages

Example iframe:
```html
<iframe src="https://artpulse.io/embed?org=42&theme=dark&layout=cards&limit=5"></iframe>
```

Script embed:
```html
<script src="https://artpulse.io/embed.js" data-org="42" data-theme="light" data-layout="list"></script>
```

## Filter Parameters
| Param   | Example                |
| ------- | ---------------------- |
| `org`   | `?org=12`              |
| `tag`   | `?tag=performance`     |
| `region`| `?region=brooklyn`     |
| `limit` | `?limit=3`             |
| `theme` | `dark` / `light`       |
| `layout`| `list`, `cards`, `grid`|
| `compact` | `true`               |

## Data & Config Model
Widgets are generated purely from URL parameters. No new tables are required but views and clicks can be logged in an optional `ap_embed_logs` table:
```sql
CREATE TABLE ap_embed_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  widget_id VARCHAR(40),
  event_id INT,
  timestamp DATETIME,
  referrer TEXT,
  action VARCHAR(10)
);
```

## Widget Builder UI
- `/tools/embed` lets org admins preview filters and layout
- Copy ready-made embed code to clipboard
- Also listed under `/wp-admin?page=org-tools#embed`

## Frontend Widget Styles
- Layout choices: **cards**, **list**, **compact**
- Responsive and scrollable in narrow containers
- Optional branding footer

## Logging & Tracking
- Pixel or `postMessage` ping when widget loads
- Click events fire logging endpoint
- Stats available at `/wp-admin?page=org-embed-stats`

## QA Checklist
- [x] Embed loads without login
- [x] Filters work (e.g. organization only)
- [x] Style reflects parameters
- [x] Clicks tracked properly
- [x] Admin preview matches embed

## Developer Sprint Checklist
- [x] Widget iframe/script endpoint
- [x] Filter parser for query parameters
- [x] Layout templates built
- [x] Embed builder UI page
- [x] Analytics log and stats view

## Codex Docs to Update
- `embed-widgets.md`
- `partner-guides.md`
- `analytics.md`
- `api-embed.md`

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
