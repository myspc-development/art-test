---
title: ArtPulse Codex: Event Ranking
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Codex: Event Ranking

This guide outlines Sprint 3 features that add intelligence to event listings. Implementors should refer to it when building ranking computations.

## 1. Event Ranking Algorithm

Events are ranked by quality and relevance. Scores combine recent views, saves, organizer verification and artist reputation. The computed ranking is stored per event in `ap_event_rankings` and updated hourly via cron.

### Rankings Table

```sql
CREATE TABLE `ap_event_rankings` (
  `event_id` BIGINT PRIMARY KEY,
  `score` DECIMAL(5,2),
  `updated_at` DATETIME
);
```

### Cron Job

```php
add_action('ap_cron_compute_rankings', 'ap_compute_event_scores');

function ap_compute_event_scores() {
  // Pull metrics, compute score per event, insert/update table
}
```

### REST Integration

`/artpulse/v1/events` accepts `orderby=rank` and falls back to post date when no score is present. Append `?show_score=1` to expose the score in JSON for debugging.


## Developer Sprint Checklist

- [x] `ap_event_rankings` created & scores update
- [x] REST `/events?orderby=rank` works

## Docs To Update

- Data Infrastructure → `ap_event_rankings`

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
