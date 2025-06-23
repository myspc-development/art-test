Admin Quick Start Guide

Welcome to the ArtPulse Admin Quick Start Guide. This document covers installation, configuration, and all key administrative features of the ArtPulse plugin.

1. Installation & Setup

Download & Activate

Upload artpulse.zip via Plugins → Add New → Upload.

Activate the ArtPulse plugin.

General Settings (ArtPulse → Settings → General)

Set your site’s default timezone and date format.

Enable Auto-expire Past Events (see section below).

Payments (ArtPulse → Settings → Payments)

Enter your Stripe Publishable & Secret keys.

Configure membership prices in the Pricing tab.

Save changes to enable paid memberships.

2. Stripe & Payments

ArtPulse relies on Stripe for all payment processing.

Ensure your webhook endpoint (https://<your-site>/wp-json/artpulse/v1/webhook) is configured in your Stripe Dashboard.

Use the System Status panel (Settings → System Status) to view recent webhook events and errors.

3. CSV Import / Export

Where to find it:

ArtPulse → Settings → Import / Export

Import

Upload a CSV file containing Organizations, Events, Artists, or Artworks.

Map CSV columns to WordPress fields and post meta.

Preview the first few rows.

Click Import to send via REST endpoint:

POST /wp-json/artpulse/v1/import

Only administrators (manage_options) have access.

Export

Select one of the data types (Organizations, Events, Artists, Artworks).

Click Export CSV to download for offline editing or backup.

4. Membership Overrides

Where to find it:

ArtPulse → Settings → General

Toggle any of the following to bypass membership checks (useful for testing or special circumstances):

Override Artist Membership

Override Organization Membership

Override Member Membership

Ticking a toggle exempts the corresponding user type from membership requirements and fees.

5. Managing Members

ArtPulse integrates with your site’s user management:

Members are users with the member role.

Artists are users with the artist role.

Organizations are a custom post type (organization).

Navigate to Users → All Users, then filter by role. Use the Bulk Actions dropdown to:

Change roles

Send membership reminders

Deactivate/reactivate accounts

6. User Dashboard Calendar

Shortcode: [ap_user_dashboard]

Place this on any page to show:

A calendar of local or favorited events.

“Add to calendar” links.

RSVP buttons for upcoming events.

Members set their location via:

Shortcode: [ap_profile_edit]

This populates the dashboard with nearby events.

7. Auto-Expire Past Events

Where to find it:

ArtPulse → Settings → General

Auto-expire Past Events

When enabled, events with an end date before today are automatically hidden from public views.

Keeps your calendar clean without manual pruning.

8. Developer: REST API Import

For programmatic imports, use:

POST /wp-json/artpulse/v1/import
Content-Type: application/json

[
  {"title": "Event One", "date_start": "2025-07-01", ...},
  {"title": "Event Two", "date_start": "2025-08-15", ...}
]

Response:

{"created": [123, 124]}

Use this endpoint to automate bulk uploads from external systems.

9. Gutenberg: Relationship Blocks

In the Block Editor (Gutenberg), you’ll find two new blocks:

Artist ↔ Event

Link artists to events on either the Artist or Event editor screen.

Artwork ↔ Organization

Associate an artwork post with its owning organization.

Use these blocks to build richly connected content without custom code.

10. Front-end Shortcodes

Shortcode

Description

[ap_user_dashboard]

Calendar of local/favorited events for members.

[ap_profile_edit]

Form for members to update their stored location.

Copy these into any page or post to expose ArtPulse features to your users.

11. Custom Admin Columns & Sorting

On each post-type list (e.g., Events, Artists):

Additional columns (dates, statuses, membership level) are displayed.

Click any column header to sort ascending/descending.

Use Screen Options (top-right) to hide or show columns.

12. Troubleshooting

System Status (Settings → System Status)

View recent webhook deliveries and errors.

Check server requirements and plugin health.

Logs

Clear or replay webhook events from Stripe.

Import Errors

Failed CSV rows are skipped.

Verify your column mappings and row formatting.

If issues persist, contact support at support@artpulse.io.

Thank you for using ArtPulse! For more details, see the Developer Guide or our online documentation at https://docs.artpulse.io.

