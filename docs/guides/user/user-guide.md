---
title: ArtPulse User Guide
category: user
role: user
last_updated: 2025-07-20
status: complete
---
# ArtPulse User Guide

For site management details see the [Admin Usage guide](../admin/admin-usage.md). A quick
overview of features lives in the [README](../README.md).

## Event Cards

Event listings across the site use a consistent card design. Each card includes
the featured image, title, venue, address, and formatted dates. RSVP and
favorite buttons appear with their current counts so you can interact directly
from any directory, calendar popover or dashboard list.

## RSVP and Favorite Events

### How to RSVP for an Event

- Click the **RSVP** button on any event card, calendar modal, or event page.
- If you are logged in and RSVP is open, the button will say "RSVP."
- After clicking, the button changes to "Cancel RSVP."
- The RSVP count updates in real-time.

### Canceling Your RSVP

- Click "Cancel RSVP" on the event card or page.
- The count updates instantly.

### How to Favorite an Event

- Click the heart (♡) on the event card, event page, or calendar.
- Favorited events are shown with a filled heart (❤).
- The favorite count updates instantly.
- All your favorited events are visible in your dashboard under "My Events."
- Event cards in "My Events" include badges showing whether you RSVP’d or favorited them.

### Following Artists and Organizations
- Click the **Follow** button on an artist, organization or event page to add it to your follows list.
- Your followed items appear in the `[ap_my_follows]` page if enabled by the site admin.
- Following creators allows you to receive notifications about new content.
- Favoriting is for single events or artworks you like right now. Following a creator keeps you updated on everything they publish going forward.

## Sharing Events and Profiles

- Click **Share** on an event, artwork or user profile to reveal quick links.
- Facebook, Twitter/X, WhatsApp and email are supported out of the box.
- If your browser supports it, a **Native Share** button appears for one-tap sharing via the device menu.
- Share buttons are shown on event cards, individual artwork pages and artist/organization profiles.
- Each share is logged and contributes to the item’s trending score.

## My Events Dashboard

- Open your **My Events** dashboard from the main menu.
- The list shows every event you have RSVPd to or favorited.
- Your next upcoming RSVP is highlighted at the top.
- Use the **Toggle Dark Mode** button or drag widgets by their handle to customize the layout. Click **Reset Layout** to restore the default arrangement for your role. Use **Export** and **Import** to share dashboard templates.
- Use the **Add Widget** button to enable widgets that are missing from your layout.

All dashboards for members, artists and organizations are rendered with the `[ap_user_dashboard]` shortcode. Default layouts for each role come from the `ap_dashboard_widget_config` option.

## Analytics in Your Dashboard

- Charts visualize your total RSVPs and favorites over time.
- See which events generated the most engagement at a glance.


## Searching and Filtering Events

- Use the search and filter form above the event/artwork/artist directories to narrow results instantly.
- Filters include: **venue, date, category, and keyword.**
- The event list updates live—no page reload needed.
- Click "Reset" to see all results again.

**Tip:** All filters are available on mobile and desktop.

## Using the Event Calendar

### Viewing Events on the Calendar

- The calendar view shows all upcoming events by day.
- Click any event to see a popup with venue, address, time, and RSVP/favorite options.
- Filter events by venue, category, or date using the filter bar above the calendar.

### Embedding the Calendar

- To embed the calendar on any page, use the shortcode:
[ap_event_calendar]
- You can also add filters or adjust display options via shortcode attributes (see below for full list).

The calendar relies on the FullCalendar library. Scripts are loaded from the jsDelivr CDN with a bundled fallback if the CDN is unreachable.

**Tip:** Calendar updates instantly when new events are added or when filters are applied.

## Dashboard Panels

### Favorites
The **Favorites** tab shows a paginated grid of everything you have saved.
Click the heart to remove an item; changes are reflected immediately.

### My RSVPs
Your upcoming RSVPs appear in a simple table. Use the **Cancel** button if
you can no longer attend.

### Portfolio
Artists can upload images, add captions and drag to reorder items in the
**Portfolio** tab. Alt text is required for accessibility.

### Events
Organizations manage their events under the **Events** tab where new events
can be created, duplicated or cancelled.

## Dashboard Navigation

The dashboard uses hash-based routes so panels can be bookmarked or linked directly:

- Members: `#overview`, `#calendar`, `#favorites`, `#my-rsvps`, `#settings`
- Artists: `#overview`, `#portfolio`, `#artworks`, `#calendar`, `#settings`
- Organizations: `#overview`, `#events`, `#rsvps`, `#analytics`, `#settings`

Adding a new panel requires registering a route in `assets/js/ap-user-dashboard.js`, creating a module that renders its contents, and exposing a navigation link. Each panel should render into `<main id="ap-view">` and avoid injecting unescaped HTML.

![Dashboard screenshot placeholder](../../images/dashboard-placeholder.png)

## Shortcode Reference

- `[ap_event_directory]` — Show event directory with filters
- `[ap_event_calendar]` — Show event calendar
- `[ap_artist_directory]` — Show artist directory
- `[ap_artwork_directory]` — Show artworks directory
- `[ap_org_directory]` — Show organizations directory
See the [README](../README.md) for more shortcode examples.

## Settings Tab Layout

Administrators manage plugin options on a dedicated **ArtPulse → Settings**
screen. Settings are organized into horizontal tabs such as **General**,
**Location APIs** and **Import/Export**. Use the links at the top of the page to
switch between tabs. The URL hash changes (for example `#general`) so you can
bookmark or share a direct link to any section.

## Accessibility & Mobile

- All features and filters work on mobile, tablet, and desktop.
- User registration falls back to normal form submission if JavaScript is disabled.
- The registration form requires entering the password twice for confirmation.
- Buttons and forms support keyboard navigation and screen readers.
- Event cards and dashboards are designed for responsive display.

> 💬 *Found something outdated? [Submit Feedback](../../feedback.md)*

