# Institutional RSVP & Visit Tracking

Audience: Dev, PM

Status: [🚧 Sprint Needed]

This module extends the standard RSVP features so schools and other institutions can register group visits and record on‑site attendance.

## REST Endpoints

`POST /wp-json/artpulse/v1/checkin`
: Record a visit for an event. Accepts `event_id`, optional `institution` name and `group_size`.

`GET  /wp-json/artpulse/v1/event/{id}/visits`
: List all recorded visits for an event. Requires organizer permissions.

`GET  /wp-json/artpulse/v1/event/{id}/visits/export`
: Download visits as CSV for grant reporting.

## Developer Checklist
- Table `ap_event_checkins` stores event ID, user ID, institution, group size and timestamp.
- `VisitTracker` installs the table on activation and logs visits.
- `VisitRestController` exposes the REST routes above.
- CSV export streams rows with `fputcsv()`.

See also: [qr-checkins.md](./qr-checkins.md), [reporting-exports.md](./reporting-exports.md)
