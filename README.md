# ArtPulse Management Plugin

For end‑user instructions see the [User Guide](docs/guides/user/README.md).
Site administrators should consult the [Admin Guide](docs/guides/admin/README.md).
> 🛠️ NOTE: Guides are now nested under /docs/guides/

## Documentation
- [Architecture Docs](docs/architecture/)
  - [Role-Based Dashboard Builder](docs/architecture/Role_Based_Dashboard_Builder.md)
- [Guides](docs/guides/)
- [Internal Docs](docs/internal/)

[Monetization & Ticketing Codex](docs/guides/developer/monetization-ticketing-codex.md).
For artist monetization options see the
[Artist Monetization Tools Codex](docs/guides/developer/artist-monetization-tools-codex.md).
For artist dashboards and audience metrics see the
[Analytics & Insights Codex](docs/guides/developer/analytics-insights-codex.md).
For admin and organization reporting plus per-user metrics see the
[Analytics & Reporting Codex](docs/guides/developer/analytics-reporting-codex.md).
For UI customization, white‑labeling and onboarding guidelines see the
[UI/UX Polish Codex](docs/guides/developer/ui-ux-polish-codex.md).

See the [Changelog](docs/CHANGELOG.md) for release notes.
[Widget Chapters](docs/codex-chapters/README.md) organize the chat, ticketing and Q&A widgets.
[Event Comments Codex](docs/event-comments-codex.md) describes the REST routes used for event discussions.
[Event Chat Codex](docs/event-chat-codex.md) explains the real-time chat module.
[Event Management Upgrades Codex](docs/event-management-upgrades-codex.md) explains calendar and bulk actions.
[Event Template & Duplication Codex](docs/event-template-duplication-codex.md) covers template storage and cloning helpers.
[Messaging & Communication Codex](docs/messaging-communication-codex.md) covers direct messaging (see the new `/messages` REST API section), reminders and comment moderation.
[Unified Inbox](/docs/api-reference.md#get-artpulsev1inbox) aggregates notifications, messages and RSVP activity via `/inbox`.
[Enhanced Notifications Codex](docs/enhanced-notifications-codex.md) details the inbox endpoints, notification preferences and onboarding tour.
[Finance & Payout Codex](docs/financial-overview-codex.md) explains revenue summaries, transaction filtering and payout history.
[Internal Notes & Task Tracking Codex](docs/internal/internal-notes-tasks-codex.md) covers collaboration features.
[Moderation & Flagging Codex](docs/moderation-flagging-codex.md) details the optional reporting workflow and admin review screens.
[Mapping & Q&A Threads Codex](docs/mapping-qa-codex.md) covers the new discovery map and event discussion threads.
[Webhook Automations Codex](docs/webhook-automation-codex.md) describes webhook management and payload delivery.
[Dashboard Menu Codex](docs/dashboard-menu-codex.md) explains how the sidebar menu is generated.
[Widget Settings Codex](docs/widget-settings-codex.md) describes widget schemas and per-user storage.
[Dashboard Builder Guide](docs/widgets/widget-matrix-reference.md) explains how to configure role-based dashboards. The old Widget Editor documentation has been removed.
[User Dashboard Customization](docs/guides/user/dashboard-customization.md) explains per-user layout storage and REST endpoints.
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
- [ArtPulse Member Dashboard Roadmap](docs/ArtPulse_Member_Dashboard_Roadmap.md)
- [Organizer Dashboard Roadmap](docs/Organizer_Dashboard_Roadmap.md)
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

## Project Vision & Goals

ArtPulse aims to streamline event management and community engagement. The
roadmap highlights priorities around security, NLP‑powered tagging,
customizable dashboards and comprehensive testing to ensure reliable
releases.

## Developer Prerequisites

Before running the plugin locally make sure the following tools are available:

- **PHP 8.2+ with Composer** – install PHP libraries with `composer install`.
- **Node.js and npm** – run `npm install` to fetch packages including
  `sass` used for SCSS compilation. If the Puppeteer download fails set
  `PUPPETEER_SKIP_DOWNLOAD=true` before installing.
- **WordPress test library** – run `bash scripts/setup-env.sh` once to
  download the WordPress core files required by the PHPUnit suite.

## Quick Start

```bash
composer install
npm install
npm run dev
```

## Directory Structure

- `src/` – PHP classes
- `admin/` – admin pages and settings
- `assets/` – JavaScript and CSS
- `tests/` – PHPUnit and Jest tests
- `docs/` – project documentation

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed contribution guidelines.

## Installation

This plugin uses [Composer](https://getcomposer.org/) for its PHP libraries.
Run the following command in the plugin directory **before** activating it so
`vendor/autoload.php` is generated:

```bash
composer install --no-dev --optimize-autoloader
```

Install the Node dependencies if you need to compile JavaScript or run the test
suite. If Chrome cannot be downloaded for Puppeteer-based tests, set
`PUPPETEER_SKIP_DOWNLOAD=1` before running `npm install`:

```bash
PUPPETEER_SKIP_DOWNLOAD=1 npm install
```

ArtPulse requires **PHP 8.2 or higher**. Ensure your server meets this
requirement before activating the plugin.

WordPress will display an “unexpected output” warning if this file is missing.
If you skip `composer install` before activating the plugin, admin pages may
return HTTP 500 errors because the required classes cannot be loaded.

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

### Shortcodes & Widgets

See [Shortcodes & Widgets Overview](docs/shortcodes-overview.md) for the complete list of available shortcodes and widgets.

## CSV Import/Export

Instructions for uploading CSV files, mapping columns and downloading exports have moved to [docs/import-export.md](docs/import-export.md).
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

Event and artist directories now render an initial page server-side for improved SEO. Filters submit via GET so results work without JavaScript. Theme developers can override `templates/event-card.php` and `templates/page-event-directory.php` by copying them into their theme.

## Artwork Directory Shortcode

The `[ap_artwork_directory]` shortcode displays artwork posts in a filterable grid. Each listing now outputs the work's medium and style beneath the title when those values are available.

Event directory pages output [schema.org](https://schema.org/) JSON‑LD using the `Event` and `ItemList` types so listings qualify for Google Rich Results. Event images use `loading="lazy"` and AJAX “Load More” pagination keeps long lists performant.

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



## Member Badges and Login Heatmap

Administrators can assign badges to users directly from their profile pages. Edit a user and enter any badge slugs as a comma-separated list in the **User Badges** field.

A new **Login Heatmap** submenu under **ArtPulse → Settings** displays hourly login counts for the last seven days so you can quickly spot peak activity times.

## Admin Pages

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

Synced Event portfolio pages can also display related artist and organization projects as a carousel. The template `templates/salient/portfolio-related-carousel.php` outputs the markup and requires the `ap-related-carousel.js` script which is enqueued by `EnqueueAssets::enqueue_frontend()` when viewing a single portfolio item.

## Portfolio synchronization

Artist, artwork, event **and organization** posts automatically create or update a corresponding portfolio entry when saved. These portfolio items are used by the directory shortcodes. Deleting the source post removes the portfolio entry as well.
Each portfolio stores the source post type in `_ap_source_type` and receives a label in the `project-category` taxonomy ("Event", "Artist" or "Organization" by default).

Portfolio entries also copy project categories and tags to the Salient taxonomies and store the post content in `_nectar_portfolio_extra_content`. When a portfolio item is edited in the admin area the changes are pushed back to the original post.

Use `wp ap migrate-portfolio` to migrate legacy `artpulse_portfolio` posts to the Salient `portfolio` type.

### Sync & Migration Tools

Administrators can run a full synchronization or migrate legacy items from **Settings → Portfolio Sync**. After running either action the page shows a notice summarizing how many items were processed. Logs of the last 50 operations are available from the same menu.

CLI equivalents are `wp ap sync-portfolio` and `wp ap migrate-portfolio`. Both commands require a user with the `manage_options` capability (use `--user=1`).

If you encounter errors check the sync logs for messages like duplicate entries or missing images. Clearing the object cache with the built‑in button ensures the front‑end reflects new portfolio data.

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

Developer environment steps, WebSocket configuration and CI instructions are documented in [docs/development-setup.md](docs/development-setup.md). To quickly spin up a local site with Docker follow [docs/Plugin_Activation_Guide.md](docs/Plugin_Activation_Guide.md).

## Running Tests

PHPUnit relies on the official WordPress test library. Run the installer to download WordPress and set up the tests, then execute PHPUnit:

```bash
bin/install-wp-tests.sh wordpress_test root '' localhost latest
composer install
composer test
```

End-to-end browser tests can be run with Cypress once the environment variables
are set. Use the provided npm script:

```bash
npm run cypress:run
```

Refer to [docs/End_to_End_Testing_Guide.md](docs/End_to_End_Testing_Guide.md) for
details on the required credentials and optional interactive mode.

See [docs/development-setup.md](docs/development-setup.md) for the required environment variables and instructions on using a local WordPress archive when working offline.

## Widget Development

Widget PHP files register themselves on the `artpulse_register_dashboard_widget` hook. Copy any file from `templates/widgets/` into your theme to override the markup. For example:

```bash
mkdir -p $(wp eval 'echo get_stylesheet_directory();')/templates/widgets
cp templates/widgets/donations.php $(wp eval 'echo get_stylesheet_directory();')/templates/widgets/
```

Developers may modify the registry via the `ap_dashboard_widgets` filter. Each widget entry supports a `section` key for grouping under headings such as **insights** or **actions**.

### Seeding Demo Content

Run the helper script to create demo users, events and donations:

```bash
wp eval-file tools/seed-dashboard-users.php
```

This populates a sample organization with members, events and donation records for local testing.

### Creating Widgets

1. Copy `widgets/SampleHelloWidget.php` and update the class name.
2. Implement `get_id()`, `get_title()`, `get_section()`, `can_view( $user_id )` and `render( $user_id )`.
3. Call `DashboardWidgetRegistry::register()` in the `register()` method with a `roles` array.

### Template Overrides

Place a file under `your-theme/widgets/` with the same name as the plugin template (e.g. `donations.php`) to override it. If no override exists the template bundled with the plugin is loaded.

### Widget Fallbacks

Wrapper functions inside `widgets/stubs.php` call implementation functions like
`ap_widget_example()`. If those inner functions are missing, stubs are
auto-generated from `widgets/fallback-stubs.php`. When `WP_DEBUG` is on, using a
stub logs a warning so developers know to supply the real widget code.

### Running PHPUnit

The suite depends on the WP test library. Run the setup script once and then execute PHPUnit:

```bash
bash scripts/setup-env.sh
composer test
```

## Sprint History

All Codex modules and tasks have been completed and verified. The project board reflects a final status of **Done** across reporting, curator tools, analytics, and testing. Documentation was updated each sprint and the changelog records feature milestones through version 1.3.16.
## Third-Party Licenses
See NOTICE for licensing details of bundled libraries.


## Status

✅ Status: Completed (as of 2025-07-19)
