# ArtPulse Management Plugin

## CSV Import/Export

An Import/Export tab is available under **ArtPulse → Settings**. From this tab administrators can:

- Upload a CSV file and map its columns to post fields or meta keys. After selecting the mappings a preview of the first few rows is displayed before importing.
- Import data in chunks via the REST API endpoint `artpulse/v1/import`.
- Download CSV exports for organizations, events, artists and artworks.

During import you can configure:

* Whether the uploaded file contains a header row.
* Which delimiter to use (comma, semicolon, tab or a custom character).
* How many initial rows should be skipped before parsing.

Only users with the `manage_options` capability may perform imports. PapaParse is used client side for parsing CSV files.
## Membership Overrides

The **ArtPulse → Settings** page includes options allowing administrators to bypass membership checks and fees for specific user types. Enabling these checkboxes disables enforcement for the corresponding role:

- **Override Artist Membership** – Artists can access paid features without an active membership.
- **Override Organization Membership** – Organizations are exempt from membership requirements and fees.
- **Override Member Membership** – Regular members are exempt from membership checks.

## Event Auto-Expiration

When **Auto-expire Past Events** is enabled under **ArtPulse → Settings**, a daily cron job runs and moves any `artpulse_event` posts whose end date is before today to Draft status. This keeps old events from appearing on the site automatically.

## Directory Shortcodes

Use the new directory shortcodes to display listings without specifying a type attribute:

- `[ap_event_directory]`
- `[ap_artist_directory]`
- `[ap_artwork_directory]`
- `[ap_org_directory]`

The older `[ap_directory]` shortcode is still supported for backward compatibility.

## Event Enhancements

Events can now include multiple co-host artists via a "Co-Host Artists" selector.
A new "Recurrence Rule" field accepts iCal strings for repeating events.

All event listings share a unified card layout. Each card shows the featured
image, title, venue, address and formatted dates, along with RSVP and favorite
buttons. The same markup powers directories, calendar popovers and dashboard
lists so the look and functionality remain consistent across the site.

## Login Shortcode

Use `[ap_login]` to display the front-end login form. When the free
[Nextend Social Login](https://wordpress.org/plugins/nextend-facebook-connect/)
plugin is installed you can enable Google, Facebook and Apple buttons under
**ArtPulse → Settings**. Any enabled providers will appear above the form and
their access tokens are stored on the user after authentication.

Enabling **Enforce Two-Factor** in the settings requires users to activate the
[Two-Factor](https://wordpress.org/plugins/two-factor/) plugin before logging in.

### OAuth Setup

1. Install and activate the **Nextend Social Login** and **Two-Factor** plugins.
2. Create API credentials with Google, Facebook and/or Apple as described in the Nextend documentation.
3. Under **ArtPulse → Settings** enable the providers you wish to offer and optionally enforce two‑factor authentication.

## Registration Shortcode

Use `[ap_register]` to display a standalone registration form for new users. Signing up through this form automatically grants the “Free” membership level. After submitting the form a confirmation message appears and the user is redirected to their dashboard.

## Logout Shortcode

Use `[ap_logout]` to show a logout link. By default the link redirects to the site homepage after logging out. Provide a `redirect` attribute to send users elsewhere.

## AJAX Event Filter Form

Display a live event search form anywhere using the `[ap_event_filter]` shortcode. Users can filter events by keyword, venue, date range and category. Results update below the form without reloading the page.

```
[ap_event_filter]
```

![Screenshot: AJAX event filter form](assets/docs/images/event_filter_form.png)



## Stripe Radar

To receive early fraud warnings you must enable the **early_fraud_warning.created** webhook event in your Stripe dashboard. Configure it to post to `/wp-json/artpulse/v1/stripe-webhook` on your site. When Radar flags a charge, the plugin logs the details and emails the site administrator.

## Dashboard Caching

Queries in the organization dashboard that previously loaded all related posts on every page view now utilize WordPress transients. Cached results are stored per organization for 15 minutes and cleared whenever related posts are saved. This reduces repetitive database lookups and improves page load times, especially for organizations with large numbers of posts.

## Database Indexes

On activation the plugin adds indexes to core meta tables if they don't already exist:

- **wp_usermeta**: `ap_usermeta_key_value` on `(meta_key, meta_value)`
- **wp_postmeta**: `ap_postmeta_key_value` on `(meta_key, meta_value)`

These indexes improve lookups for membership-related keys like `ap_organization_id`, `ap_membership_level` and `ap_membership_expires`.

## Shortcode Pages Tab

Under **Settings → Shortcode Pages** you can automatically generate WordPress pages containing any of the plugin's shortcodes. Choose the shortcodes you want and the plugin creates and manages the corresponding pages for you.

## Additional Settings

Recent updates introduce new fields for location lookups and performance tweaks:

- **Geonames Username** – Username for the Geonames API.
- **Google Places API Key** – Key for Google Places requests.
- **Enable Service Worker** – Adds a basic service worker for offline caching.
- **Enable Google/Facebook/Apple Login** – Toggle OAuth providers for the login form.
- **Enforce Two-Factor** – Require users to enable the Two-Factor plugin before logging in.

For full usage details refer to the [Admin Guide](docs/admin-guide.md) and [User Guide](docs/user-guide.md).

## Member Badges and Login Heatmap

Administrators can assign badges to users directly from their profile pages. Edit a user and enter any badge slugs as a comma-separated list in the **User Badges** field.

A new **Login Heatmap** submenu under **ArtPulse → Settings** displays hourly login counts for the last seven days so you can quickly spot peak activity times.

## User Dashboard Features

Place `[ap_user_dashboard]` on a page to expose the member dashboard. The dashboard displays calendars for **My Favorites** and **My RSVPs** along with charts of engagement statistics. A bar chart shows monthly trends while a new line chart visualizes daily activity over the last 30 days. Users can quickly access profile tools, download their data and review upcoming events.

![Screenshot: member dashboard](assets/docs/images/member_dashboard.png)


## Submission Meta Fields

Artist and organization submissions automatically copy the post title into a
matching meta field. The artist form sets `artist_name`, while organization
submissions populate `ead_org_name`. This makes it easier to search by name in
the admin area and via the REST API.

## Portfolio Builder

Use `[ap_portfolio_builder]` to allow members to create portfolio items. Drag items in the saved list to reorder them; changes are saved automatically.

## Portfolio Meta Migration

Older versions saved portfolio links and visibility using the meta keys `_ap_portfolio_link` and `_ap_visibility`. These have been renamed to `portfolio_link` and `portfolio_visibility`. When the plugin loads in the admin area it copies any existing values to the new keys and removes the legacy ones.


## Event Gallery

Single event pages display gallery images using [Swiper](https://swiperjs.com/). The minified assets live in `assets/libs/swiper/` and are enqueued only when viewing an individual event. A small initialization script sets up the slider targeting `.event-gallery`.

Organization posts reuse this system. The same `ap-event-gallery-front.js` script loads when viewing an individual organization and initializes the slider if IDs are stored in the `_ap_submission_images` field.

## Portfolio Gallery

Portfolio posts mirror this functionality. When a portfolio item contains multiple IDs in the `_ap_submission_images` meta field, each ID after the featured image outputs as a slide. The front-end script `ap-portfolio-gallery-front.js` initializes Swiper on single portfolio pages. If no gallery images are present a short “No gallery images available.” message is displayed.

## Uninstall Cleanup

When the plugin is uninstalled through the WordPress admin, all tables created by ArtPulse are removed automatically. The following tables are dropped along with the `artpulse_settings` option:

- `{prefix}artpulse_data`
- `{prefix}ap_favorites`
- `{prefix}ap_follows`
- `{prefix}ap_notifications`
- `{prefix}ap_profile_metrics`
- `{prefix}ap_link_requests_meta`
- `{prefix}ap_roles`
- `{prefix}ap_login_events`
- `{prefix}ap_artwork_event_links`

## RSVP & Favorites

### How to RSVP for an Event

- Click the **RSVP** button on any event card, calendar modal, or event page.
- If you are logged in and RSVP is open, the button will say "RSVP."  
- After clicking, the button changes to "Cancel RSVP."  
- The RSVP count updates in real-time.

### Canceling Your RSVP

- Click "Cancel RSVP" on the event card or page.
- The count updates instantly.

### How to Favorite an Event

- Click the star (☆) on the event card, event page, or calendar.
- Favorited events are shown with a filled star (★).
- The favorite count updates instantly.
- All your favorited events are visible in your dashboard under "My Events."

### "My Events" Dashboard

- Go to your dashboard to see:
  - **All RSVP’d events**
  - **All favorited events**
  - Your **next upcoming RSVP** is highlighted
  - Engagement stats and trends (chart)

## AJAX-Powered Search & Filtering

- Use the search and filter form above the event/artwork/artist directories to narrow results instantly.
- Filters include: **venue, date, category, and keyword.**
- The event list updates live—no page reload needed.
- Click "Reset" to see all results again.

**Tip:** All filters are available on mobile and desktop.

## Managing RSVPs & Attendees (For Organizers/Admins)

### Viewing and Exporting RSVPs

- In your dashboard, go to the event you are managing.
- Click "View RSVPs" to see all attendee details (name, email, RSVP date, attendance status).
- Click "Export RSVPs to CSV" to download a spreadsheet of attendees.

### Marking Attendance

- Check/uncheck the "Attended" box next to each attendee.
- Attendance stats update in real time and appear in event analytics.

### Messaging Attendees

- Use the "Message All" button to email all RSVP’d users at once.
- Use the mail/message icon next to any attendee to send a message to a single user.
- Messages use your default notification settings.

### Waitlist (If Enabled)

- If an event is full, new RSVPs are added to a waitlist.
- When a slot opens, waitlisted users are notified automatically.

## Engagement Analytics

- Analytics dashboards appear for both users and organizers.
- Widgets show:
  - **Total RSVPs** (overall and per event)
  - **Favorites** (total and per event)
  - **Attendance** (marked "attended" for each event)
  - **Trends over time** (line/bar charts powered by Chart.js)

### How to Read Analytics

- Hover over any point on a chart to see exact counts.
- View "top events" by RSVP/favorite count in the analytics list.
- Export data as CSV for your own reporting.

## Event Calendar

### Viewing Events on the Calendar

- The calendar view shows all upcoming events by day.
- Click any event to see a popup with venue, address, time, and RSVP/favorite options.
- Filter events by venue, category, or date using the filter bar above the calendar.

### Embedding the Calendar

- To embed the calendar on any page, use the shortcode:  
[ap_event_calendar]

- You can also add filters or adjust display options via shortcode attributes (see below for full list).

**Tip:** Calendar updates instantly when new events are added or when filters are applied.

## Shortcode Reference

- `[ap_event_directory]` — Show event directory with filters
- `[ap_event_calendar]` — Show event calendar
- `[ap_artist_directory]` — Show artist directory
- `[ap_artwork_directory]` — Show artworks directory
- `[ap_org_directory]` — Show organizations directory

See docs/user-guide.md for all shortcode options and attributes.

## Accessibility & Mobile

- All features and filters work on mobile, tablet, and desktop.
- Buttons and forms support keyboard navigation and screen readers.
- Event cards and dashboards are designed for responsive display.

