# ArtPulse Codex: Event Management Upgrades

This guide describes optional modules for calendar integration, bulk actions and draft scheduling. It helps developers extend event workflows for artists.

## 1. Calendar Integration

Expose an interactive calendar showing all of an artist's events. Use FullCalendar or a similar library. A REST endpoint should return each event with status, title, start and end dates, and a color used by the calendar UI.

Example JavaScript:

```html
<div id="artist-events-calendar"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var el = document.getElementById('artist-events-calendar');
  if (!el || !window.FullCalendar) return;
  var cal = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    events: '/api/artist-events',
    eventClick: function(info) {
      window.location.href = '/dashboard/events/edit/' + info.event.id;
    }
  });
  cal.render();
});
</script>
```

## 2. Bulk Actions

When viewing **My Events**, artists can select multiple events and apply an action. Typical actions include closing RSVPs, duplicating, deleting or exporting attendees. A form with check boxes submits the selected IDs and a chosen action to `/dashboard/events/bulk`. Use nonces for security and log actions for auditing.

## 3. Drafts & Scheduling

Event forms may offer three publishing states:

- **Publish Now** – event is immediately public
- **Save as Draft** – stored as `draft` until edited
- **Schedule** – stored as `future` with a publish date

In the dashboard and calendar, clearly mark draft or scheduled events. When saving, set `post_status` and optionally `post_date` according to the selected option.

Update this codex whenever new statuses or actions are added.

## Calendar Feeds

Users can subscribe to upcoming events via iCal. Endpoints return `.ics` files:

| Feed | Path |
|------|------|
| Artist | `/feeds/ical/artist/{id}.ics` |
| Gallery | `/feeds/ical/gallery/{id}.ics` |
| All Events | `/feeds/ical/all.ics` |

Buttons on event pages link to Google Calendar and an `.ics` download for Apple or Outlook.
