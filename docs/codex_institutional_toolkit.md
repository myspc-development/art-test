# Institutional RSVP & Visit Tracking

Audience: Dev, PM

Status: [🟢 Ready]

This module extends the standard RSVP features so schools and other institutions can register group visits and record on‑site attendance.

## Database Schema

```sql
CREATE TABLE ap_event_checkins (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  event_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  institution VARCHAR(191) DEFAULT '',
  group_size SMALLINT UNSIGNED DEFAULT 1,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY event_id (event_id)
) DEFAULT CHARSET=utf8mb4;
```

The table stores each visit entry. `institution` is freeform text so teachers can input the school name. `group_size` records how many students attended.

## REST Endpoints

| Method | Route | Permission | Description |
| ------ | ----- | ---------- | ----------- |
| `POST` | `/wp-json/artpulse/v1/checkin` | `rsvp_events` or public | Record a class visit. Body: `event_id`, `institution`, `group_size` |
| `GET`  | `/wp-json/artpulse/v1/event/{id}/visits` | `manage_events` | List visits for an event |
| `GET`  | `/wp-json/artpulse/v1/event/{id}/visits/export` | `manage_events` | CSV export of visit log |
| `GET`  | `/wp-json/artpulse/v1/institutions` | `manage_events` | Autocomplete for known institution names |

All endpoints return JSON objects with success or error messages. The export route streams the CSV so large datasets don't exhaust memory.

## UI Wireframes

```
-----------------------------------------
| Class Visit Check-In                 |
|---------------------------------------|
| Institution:  [______________]        |
| Group Size:  [  20 ]                  |
| [Save Visit]                          |
-----------------------------------------

-----------------------------------------
| Event Visit Log                       |
|---------------------------------------|
| [Search] [Event ▼] [Export CSV]       |
|                                       |
| Date       Institution      Group     |
| 03/12      Lakeside High    18        |
| 03/13      Westmont College 25        |
-----------------------------------------
```

The first screen appears on the event edit page for organizers to quickly record class attendance. The second is a paginated log listing all visits with an export button.

## Developer Checklist
- Table `ap_event_checkins` stores event ID, user ID, institution, group size and timestamp.
- `VisitTracker` installs the table on activation and logs visits.
- `VisitRestController` exposes the REST routes above.
- CSV export streams rows with `fputcsv()`.

See also: [qr-checkins.md](./qr-checkins.md), [reporting-exports.md](./reporting-exports.md)
