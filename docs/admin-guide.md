# ArtPulse Admin Guide

See the [User Guide](user-guide.md) for instructions that attendees follow when interacting with events and the calendar. For a feature overview refer to the [README](../README.md).

## RSVP Management

- Open the event in your dashboard and click **View RSVPs**.
- Mark each attendee as **Attended** or leave unchecked.
- Use **Export RSVPs to CSV** to download a spreadsheet of all responses.

## Attendee Messaging

- Click **Message All** to email every RSVP'd user.
- Use the message icon next to any attendee to send a single message.
- Emails use your configured notification settings.
- Customize the default email template under **ArtPulse → Settings**.
- When using the Mailgun method, enter your domain and API key under **Settings → Emails**.

## Event Analytics

- Dashboards visualize total RSVPs, favorites and attendance.
- Charts show trends over time and top events at a glance.
- Data can be exported as CSV for further analysis.
- Ticket purchases automatically email a PDF ticket to the buyer.

## Embedding the Event Calendar

Use the `[ap_event_calendar]` shortcode to place the calendar anywhere on the site. Shortcode attributes let you filter by venue, category, and date or change display options.

## Settings Tab Layout

All plugin options live under **ArtPulse → Settings** in the admin menu. The
page is divided into tabs. Click **General**, **Location APIs**, **Import/Export**,
**Config Backup**, **Updates** or **Shortcode Pages** to reveal the fields for each area. The active tab is
tracked via the URL hash so you can share a direct link like `#/import_export`.

### Config Backup

The **Config Backup** tab lets you download a JSON file of all plugin options or
restore settings from a previous backup. Click **Download Backup** to export the
current configuration and use the upload form to import a saved file. Export a
backup before upgrading so you can quickly restore the configuration if needed.

### Updates

The **Updates** tab allows administrators to pull the latest plugin code
directly from a GitHub repository. Specify the repository URL, branch or
release and optional access token. You can manually check for updates or enable
automatic daily checks. Upload new versions over the existing plugin folder or
use this tab—settings are preserved because the uninstall process never runs
during an upgrade. The **Keep Data on Uninstall** option ensures content
remains if you remove the plugin entirely. After each successful update, an
unordered list of the files that changed appears beneath the success notice.

### Shortcode Pages

The **Shortcode Pages** tab lets you quickly generate WordPress pages
containing any of the plugin's shortcodes. Check the shortcodes you want
to test and click **Create Pages**. You can then enable or disable the
generated pages with a single click when verifying new features.

### Copy Templates to Child Theme

The **Copy Templates to Child Theme** button on the Settings page copies the
default event templates from the plugin into your active child theme. After
clicking it, the files appear under `templates/salient/` inside the child
theme directory so you can customize them without modifying the plugin
directly.

### Location APIs

Use this tab to configure geocoding services. Enter your **Google Places API
Key** here so the autocomplete endpoint can query Google. If the key is missing
requests to `/wp-json/artpulse/v1/location/google` return a 400 error.

## Accessibility & Mobile Notes

All admin tools are responsive and keyboard accessible so you can manage events from any device.

## Refresh Permalinks After Updates

When upgrading the plugin or making changes to custom taxonomies, WordPress may
not immediately register the updated rewrite rules. To avoid 404 errors on REST
endpoints like `/wp/v2/artwork_style` and `/wp/v2/event_type`, either
reactivate the plugin or visit **Settings → Permalinks**. This flushes rewrite
rules so the new routes work correctly.

## Admin Pages

**Roles & Permissions** – Manage organization roles and capabilities. Access the
screen via `wp-admin/admin.php?page=ap-org-roles` from the Organization
Dashboard menu. Visiting `/wp-admin/ap-org-roles` directly will return a 404
because the route is handled through `admin.php`.
