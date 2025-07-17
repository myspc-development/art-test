# ArtPulse Management Plugin

For end‑user instructions see the [User Guide](docs/user-guide.md). Site
administrators should consult the [Admin Guide](docs/admin-guide.md).
Developers extending reporting or reminder features can reference the
[Admin & Analytics Codex](docs/admin-analytics-codex.md). Developers
implementing ticket sales or memberships can read the
[Monetization & Ticketing Codex](docs/monetization-ticketing-codex.md).
For artist monetization options see the
[Artist Monetization Tools Codex](docs/artist-monetization-tools-codex.md).
For artist dashboards and audience metrics see the
[Analytics & Insights Codex](docs/analytics-insights-codex.md).
For admin and organization reporting plus per-user metrics see the
[Analytics & Reporting Codex](docs/analytics-reporting-codex.md).
For UI customization, white‑labeling and onboarding guidelines see the
[UI/UX Polish Codex](docs/ui-ux-polish-codex.md).
[Widget Chapters](docs/codex-chapters/README.md) organize the chat, ticketing and Q&A widgets.
[Event Comments Codex](docs/event-comments-codex.md) describes the REST routes used for event discussions.
[Event Chat Codex](docs/event-chat-codex.md) explains the real-time chat module.
[Event Management Upgrades Codex](docs/event-management-upgrades-codex.md) explains calendar and bulk actions.
[Event Template & Duplication Codex](docs/event-template-duplication-codex.md) covers template storage and cloning helpers.
[Messaging & Communication Codex](docs/messaging-communication-codex.md) covers direct messaging (see the new `/messages` REST API section), reminders and comment moderation.
[Unified Inbox](/docs/api-reference.md#get-artpulsev1inbox) aggregates notifications, messages and RSVP activity via `/inbox`.
[Enhanced Notifications Codex](docs/enhanced-notifications-codex.md) details the inbox endpoints, notification preferences and onboarding tour.
[Finance & Payout Codex](docs/financial-overview-codex.md) explains revenue summaries, transaction filtering and payout history.
[Internal Notes & Task Tracking Codex](docs/internal-notes-tasks-codex.md) covers collaboration features.
[Moderation & Flagging Codex](docs/moderation-flagging-codex.md) details the optional reporting workflow and admin review screens.
[Mapping & Q&A Threads Codex](docs/mapping-qa-codex.md) covers the new discovery map and event discussion threads.
[Webhook Automations Codex](docs/webhook-automation-codex.md) describes webhook management and payload delivery.
[Event Ranking & Partner API Codex](docs/event-ranking-partner-api-codex.md) covers event scoring and partner access.
[Dashboard Menu Codex](docs/dashboard-menu-codex.md) explains how the sidebar menu is generated.
[Widget Settings Codex](docs/widget-settings-codex.md) describes widget schemas and per-user storage.
[User Dashboard Customization](docs/user-dashboard-customization.md) explains per-user layout storage and REST endpoints.
[Multi-org Roles & Permissions](docs/multi-org-roles-permissions.md) covers assigning members to multiple organizations.
[Community Engagement Codex](docs/community-engagement-codex.md) summarizes forum, feedback and social modules.
[Member Registration Codex](docs/member-registration-codex.md) explains native registration hooks.
[Artist & Gallery Tools Codex](docs/artist-gallery-tools-codex.md) details self-serve portals, AI tagging, CRM features and grant-ready reports.
[Mobile ArtWalk Guide](docs/mobile-artwalk.md) explains the PWA, nearby feed and crawl planner.
[QR Check-Ins Codex](docs/qr-checkins.md) covers attendance scanning and logs.
[Curator Feedback Codex](docs/curator-feedback.md) details private notes and ratings.
[Reporting & Export Codex](docs/reporting-exports.md) summarizes analytics exports.
[Partnership Deployment Codex](docs/partnership-deployment.md) walks through onboarding city partners.

Additional resources:

- [API Reference](docs/api-reference.md)
- [Testing Strategy](docs/testing-strategy.md)
- [End-to-End Testing Guide](docs/End_to_End_Testing_Guide.md)
- [CI/CD Workflow Guide](docs/ci-workflow.md)
- [Help Resources](docs/help-resources.md)
- [Sprint Workflow Codex](docs/sprint-workflow-codex.md)
- [Sprint 4 Polish & Documentation Codex](docs/sprint4-polish-documentation-codex.md)

Styling now relies on the Salient theme and WPBakery builder. See
[`assets/docs/Style_Guide.md`](assets/docs/Style_Guide.md) for
recommended class names when extending layouts.
Dashboard widgets follow the unified styles described in the
[`Dashboard Widget Design Codex`](docs/dashboard-widget-design-codex.md)
and the more detailed
[`Default Design System Codex`](docs/default-design-system-codex.md).
When customizing SCSS or JavaScript be sure to run `npm run build` and
commit the updated `build/` output so the admin screens load the latest
compiled assets.

## Installation

This plugin uses [Composer](https://getcomposer.org/) for its PHP libraries.
Run the following command in the plugin directory **before** activating it so
`vendor/autoload.php` is generated:

```bash
composer install --no-dev --optimize-autoloader
```

WordPress will display an “unexpected output” warning if this file is missing.

## Overview & Quick Reference

### RSVP & Favorite Features

#### How to RSVP for an Event
- Click the **RSVP** button on any event card, calendar modal, or event page.
- If you are logged in and RSVP is open, the button will say "RSVP."
- After clicking, the button changes to "Cancel RSVP."
- The RSVP count updates in real-time.

#### Canceling Your RSVP
- Click "Cancel RSVP" on the event card or page.
- The count updates instantly.

#### How to Favorite an Event
- Click the star (☆) on the event card, event page, or calendar.
- Favorited events are shown with a filled star (★).
- The favorite count updates instantly.
- All your favorited events are visible in your dashboard under "My Events."

#### "My Events" Dashboard
- Go to your dashboard to see:
  - **All RSVP’d events**
  - **All favorited events**
  - Your **next upcoming RSVP** is highlighted
  - Engagement stats and trends (chart)

### AJAX Search & Filtering
- Display a live event search form anywhere using `[ap_event_filter]`. Users can filter events by keyword, venue, date range and category. Results update below the form without reloading the page. Requests are secured with a nonce automatically added to the page.
- Use the search and filter form above directories to narrow results instantly.
- Filters include: **venue, date, category, and keyword.**
- Click "Reset" to see all results again.
- **Tip:** All filters are available on mobile and desktop.

### Dashboard & Analytics
- Queries in the organization dashboard now use transients to cache results for 15 minutes, improving load times.
- Place `[ap_user_dashboard]` on a page to expose the member dashboard with calendars for **My Favorites** and **My RSVPs** along with charts of engagement statistics.
- Analytics dashboards appear for both users and organizers.
- Widgets show:
  - **Total RSVPs** (overall and per event)
  - **Favorites** (total and per event)
  - **Attendance** (marked "attended" for each event)
  - **Trends over time** (line/bar charts powered by Chart.js)
- Hover over any point on a chart to see exact counts.
- View "top events" by RSVP/favorite count in the analytics list.
- Export data as CSV for your own reporting.

### RSVP Management & Export
- In your dashboard, go to the event you are managing.
- Click "View RSVPs" to see all attendee details (name, email, RSVP date, attendance status).
- Click "Export RSVPs to CSV" to download a spreadsheet of attendees.
- Check/uncheck the "Attended" box next to each attendee.
- Attendance stats update in real time and appear in event analytics.
- If an event is full, new RSVPs are added to a waitlist.
- When a slot opens, waitlisted users are notified automatically.

### Attendee Messaging
- Use the "Message All" button to email all RSVP’d users at once.
- Use the mail/message icon next to any attendee to send a message to a single user.
- Messages use your default notification settings.

### Class Visit Check-Ins
Institutions can scan a QR code or submit a form to log visits. Each check-in records the institution name and group size for the event organizer. Visit logs can be exported as CSV from the dashboard for grant reports.

### CRM & Donor Tools
Contacts are automatically logged when members interact with your organization. RSVPs add the `rsvp` tag, follows add the `follower` tag and donations add the `donor` tag. View and export these contacts from the CRM tab in the dashboard.

### Event Calendar
- The calendar view shows all upcoming events by day.
- Click any event to see a popup with venue, address, time, and RSVP/favorite options.
- Filter events by venue, category, or date using the filter bar above the calendar.
- To embed the calendar on any page, use the shortcode:
  [ap_event_calendar]
- You can also add filters or adjust display options via shortcode attributes.
- **Tip:** Calendar updates instantly when new events are added or when filters are applied.
 - Export any event as an `.ics` file via `/events/{id}/export.ics`, download an organization's full schedule from `/org/{id}/calendar.ics`, or fetch a specific artist feed from `/artist/{id}/events.ics`. iCal downloads now include local timezone details.

### Embeddable Event Widget
Embed a list of events on any site with a script tag:

```html
<script src="https://example.com/wp-json/widgets/embed.js?type=artist&id=42&color=%23c0392b&layout=horizontal"></script>
```

Parameters:

| Name | Description |
|------|-------------|
| `type` | `artist` or `gallery` |
| `id` | Artist or organization ID |
| `color` | Hex color for link text |
| `layout` | `list` (default) or `horizontal` |

The script injects an iframe served from `/wp-json/widgets/render` with the same parameters. Responses are cached for an hour.

### Shortcodes & Widgets List
- `[ap_event_directory]` — Show event directory with filters
 - `[ap_event_calendar]` — Show event calendar
 - `[ap_event_map]` — Map view of events
- `[ap_events_slider]` — Carousel of upcoming events
- `[ap_artist_directory]` — Show artist directory
- `[ap_artwork_directory]` — Show artworks directory
- `[ap_org_directory]` — Show organizations directory
- `[ap_login]` — Front-end login form with optional OAuth buttons
- `[ap_register]` — Standalone registration form with password confirmation
- `[ap_logout]` — Logout link
 - `[ap_event_filter]` — AJAX search and filter form (secured with a nonce)
- `[ap_user_dashboard]` — Member dashboard with stats
- `[ap_portfolio_builder]` — Portfolio item builder
- `[ap_org_event_form]` — Organization event submission form
- `[ap_directory]` — Legacy directory shortcode
- `[ap_my_follows]` — List artists, events or organizations you follow
- `[ap_favorite_portfolio]` — Display a portfolio of favorited events/artworks
- `[ap_favorites_analytics]` — Show analytics for favorite counts
- `[ap_messages]` — Render threaded inbox
- `[ap_message_form]` — Standalone message compose form

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

## Artwork Directory Shortcode

The `[ap_artwork_directory]` shortcode displays artwork posts in a filterable grid. Each listing now outputs the work's medium and style beneath the title when those values are available.

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

Use `[ap_register]` to display a standalone registration form for new users. The form collects username, email, a password and confirmation. Signing up through this form automatically grants the “Free” membership level. After submitting the form a confirmation message appears and the user is redirected to their dashboard.

## Logout Shortcode

Use `[ap_logout]` to show a logout link. By default the link redirects to the site homepage after logging out. Provide a `redirect` attribute to send users elsewhere.

## WooCommerce Membership Purchases

Enable **WooCommerce Integration** under **ArtPulse → Settings** to sell memberships as WooCommerce products. The `[ap_membership_purchase]` shortcode outputs a link or coupon form directing users to the checkout page. Set the `level` attribute to `Basic`, `Pro` or `Org` and optionally pass a `coupon` code.

## AJAX Event Filter Form

Display a live event search form anywhere using the `[ap_event_filter]` shortcode. Users can filter events by keyword, venue, date range and category. Results update below the form without reloading the page.
The form includes a nonce so only legitimate pages can run the AJAX query.

```
[ap_event_filter]
```

## Events Slider Shortcode

Use `[ap_events_slider]` to display a sliding carousel of upcoming events. The slider loads events via the REST API and uses Swiper for navigation.

## Organization Event Form

The `[ap_org_event_form]` shortcode lets logged-in organizations submit new events from the front end. Submissions are saved as pending posts until reviewed.
Event submission forms support up to five images and thumbnails can be reordered via drag-and-drop.


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

## Settings Registry

The settings screen uses horizontal tabs such as **General**, **Location APIs**,
**Import/Export** and **Shortcode Pages**. Tabs and their fields are gathered via
`SettingsRegistry` before rendering. A manager registers a tab using
`SettingsRegistry::register_tab( $slug, $label )` and then calls
`SettingsRegistry::register_field( $tab, $key, $config )` for each option. The
final arrays can be filtered with `artpulse_settings_tabs` and
`artpulse_settings_fields_{slug}` if you need to add or modify options.

## Additional Settings

Recent updates introduce new fields for location lookups and performance tweaks:

- **Geonames Username** – Username for the Geonames API.
- **Google Places API Key** – Key for Google Places requests.
- **Enable Service Worker** – Adds a basic service worker for offline caching.
- **Enable Google/Facebook/Apple Login** – Toggle OAuth providers for the login form.
- **Enforce Two-Factor** – Require users to enable the Two-Factor plugin before logging in.
- **Social Auto-Posting** – Automatically share new content to Facebook, Instagram, Twitter/X and Pinterest.

### Service Worker Integration

Check **Enable Service Worker** under *ArtPulse → Settings → General* to register the plugin’s worker. The loader script `assets/js/sw-loader.js` registers `assets/js/service-worker.js` after the page loads. The worker caches the homepage for offline access and handles basic push notifications. Modify `assets/js/service-worker.js` to cache more URLs or adjust the notification logic, then refresh or unregister the old worker in your browser to apply the changes.

For full usage details refer to the [Admin Guide](docs/admin-guide.md) and [User Guide](docs/user-guide.md).

## Member Badges and Login Heatmap

Administrators can assign badges to users directly from their profile pages. Edit a user and enter any badge slugs as a comma-separated list in the **User Badges** field.

A new **Login Heatmap** submenu under **ArtPulse → Settings** displays hourly login counts for the last seven days so you can quickly spot peak activity times.

## Admin Pages

- **Roles & Permissions** – Manage organization roles and capabilities from **Org Dashboard → Roles & Permissions** (`wp-admin/admin.php?page=ap-org-roles`). The old `ap-org-roles-matrix` slug now redirects here.
- **Diagnostics** – View plugin status and run maintenance tasks. Visit `wp-admin/admin.php?page=ap-diagnostics` or use `/wp-admin/ap-diagnostics` which redirects automatically.

## User Dashboard Features

Place `[ap_user_dashboard]` on a page to expose the member dashboard. The dashboard displays calendars for **My Favorites** and **My RSVPs** along with charts of engagement statistics. A bar chart shows monthly trends while a new line chart visualizes daily activity over the last 30 days. Users can quickly access profile tools, download their data and review upcoming events.

Recent updates introduce an optional dark mode and draggable dashboard widgets. An onboarding banner guides new users through the interface. The `[ap_my_follows]` shortcode and follow buttons let members keep track of artists, events and organizations they follow.


## Submission Meta Fields

Artist and organization submissions automatically copy the post title into a
matching meta field. The artist form sets `artist_name`, while organization
submissions populate `ead_org_name`. This makes it easier to search by name in
the admin area and via the REST API.

## Review System

The plugin registers a `review` post type for feedback on artists, events,
artworks and organizations. Each review stores a `_reviewed_id` meta value along
with a `reviewed_type` term identifying the subject. Queries can filter reviews
by both fields to display relevant feedback anywhere on the site.

## Portfolio Builder

Use `[ap_portfolio_builder]` to allow members to create portfolio items. Drag items in the saved list to reorder them; changes are saved automatically. The builder now appears inside the Organization Dashboard so organizations can manage their portfolio without leaving the page. Clicking **Edit** loads the item into the form for instant title updates.

## Portfolio Meta Migration

Older versions saved portfolio links and visibility using the meta keys `_ap_portfolio_link` and `_ap_visibility`. These have been renamed to `portfolio_link` and `portfolio_visibility`. When the plugin loads in the admin area it copies any existing values to the new keys and removes the legacy ones.


## Event Gallery

Single event pages display gallery images using [Swiper](https://swiperjs.com/). The minified assets live in `assets/libs/swiper/` and are enqueued only when viewing an individual event. A small initialization script sets up the slider targeting `.event-gallery`.

Organization posts reuse this system. The same `ap-event-gallery-front.js` script loads when viewing an individual organization and initializes the slider if IDs are stored in the `_ap_submission_images` field.

## Portfolio Gallery

Portfolio posts mirror this functionality. When a portfolio item contains multiple IDs in the `_ap_submission_images` meta field, each ID after the featured image outputs as a slide. The front-end script `ap-portfolio-gallery-front.js` initializes Swiper on single portfolio pages. If no gallery images are present a short “No gallery images available.” message is displayed.

## Portfolio synchronization

Artist, artwork, event **and organization** posts automatically create or update a corresponding portfolio entry when saved. These portfolio items are used by the directory shortcodes. Deleting the source post removes the portfolio entry as well.

## Plugin Updates

Upload new versions over the existing `artpulse-management` folder or use the **Updates** tab inside Settings. Since uninstall hooks only run when deleting the plugin, all options and data remain intact during a standard upgrade. The **Keep Data on Uninstall** setting protects content if you ever remove the plugin entirely. Export a configuration backup from the **Config Backup** tab before updating so you can easily restore the options if needed.

## Uninstall Cleanup

When the plugin is uninstalled through the WordPress admin, all tables created by ArtPulse are removed automatically. Administrators can enable **Keep Data on Uninstall** under Settings → General to preserve these tables and the `artpulse_settings` option. This toggle now defaults to **on** so data survives a reinstall. Developers may also define `ARTPULSE_REMOVE_DATA_ON_UNINSTALL` in `wp-config.php` to force removal or preservation. When data removal is enabled, the following tables are dropped:

- `{prefix}artpulse_data`
- `{prefix}ap_favorites`
- `{prefix}ap_follows`
- `{prefix}ap_notifications`
- `{prefix}ap_profile_metrics`
- `{prefix}ap_link_requests_meta`
- `{prefix}ap_roles`
- `{prefix}ap_login_events`
- `{prefix}ap_artwork_event_links`


## Accessibility & Mobile

- All features and filters work on mobile, tablet, and desktop.
- Buttons and forms support keyboard navigation and screen readers.
- Event cards and dashboards are designed for responsive display.

## Development Setup

ArtPulse currently supports **PHP 8.2–8.4**. Ensure a compatible PHP version is
installed before continuing.

Install Composer dependencies and WordPress PHPUnit tools:

```bash
composer install    # PHP libraries
```

Install Node packages used for bundling JavaScript and compiling SCSS, then
build the bundled assets:

```bash
npm install
npm run build
```

The `scripts/` directory stores optional scaffolding and release helpers
used during early development. These scripts are **not** required when
setting up the plugin from source.

Before running the tests for the first time, execute the environment
setup script to fetch WordPress and install dependencies:

```bash
bash scripts/setup-env.sh
```
The script requires the `svn` command. Install Subversion if it is not available.
This script downloads WordPress and installs Composer dependencies. When the
`CI` environment variable is present it runs `composer install` with non‑interactive options, making it suitable for automated pipelines.

Run the test suite and coding standards checks with:

```bash
vendor/bin/phpunit
composer sniff
```

The `package.json` file defines scripts for building production assets. To compile the SCSS, bundle the React sources and build blocks run:

```bash
npm run build
```
PostCSS reads `postcss.config.js` from the repository root, so no additional
configuration is required under `wp-content/plugins`.
This command compiles the SCSS, bundles blocks and packages the React-based admin scripts via Rollup. Run it whenever files such as
`sidebar-taxonomies.jsx`, `advanced-taxonomy-filter-block.jsx`,
`filtered-list-shortcode-block.jsx` or `ajax-filter-block.jsx` are
modified so the corresponding `.js` files are regenerated.

To create a distributable archive execute the Composer `zip` script:

```bash
composer zip
```

The zip file is placed in the `release/` directory.

### WebSocket Server

The real-time message server uses a JWT for authentication. Set the
`JWT_SECRET` environment variable to a long random string before running the
server. You can start it with `node server/ws-server.js` or via the npm script
`npm run ws`.

To configure the server create a `.env` file in the project root. Define the
secret and optionally the port:

```bash
JWT_SECRET=change_me_to_a_long_random_string
# PORT=3001
```

`JWT_SECRET` must be at least ten characters long or the server will abort at
startup. If `PORT` is omitted the server defaults to `3001`.

#### Troubleshooting

- **Cannot connect:** ensure the server is running and that clients are using
  the correct WebSocket URL including the port value.
- **Unauthorized errors:** verify the JWT used by the client was signed with the
  same `JWT_SECRET` set for the server.
- **Connection closed immediately:** some corporate proxies and firewalls block
  WebSocket traffic. Try another network or adjust proxy settings.

### Test Environment Variables

Database credentials for the WordPress test suite must be supplied via
environment variables. Define these before running `bash scripts/setup-env.sh`
or `vendor/bin/phpunit`:

- `DB_NAME` – name of the test database
- `DB_USER` – database user
- `DB_PASSWORD` – user password
- `DB_HOST` – database host
- `DB_CHARSET` – optional character set
- `DB_COLLATE` – optional collation

You may also set `WORDPRESS_TARBALL` to the path of a local WordPress archive
if you need an offline setup. If any variables are omitted the test bootstrap
will fail to connect to the database.

### CI Workflow

The `plugin-release.yml` workflow in `.github/workflows/` installs Composer and
npm dependencies, runs unit tests and builds production assets. Composer caches
are stored under `~/.composer/cache` while npm caches use `~/.npm`. The cache
keys include `composer.lock` and `package-lock.json` so dependencies are
re-downloaded only when these files change.

