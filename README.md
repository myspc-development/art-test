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

## Login Shortcode

Use `[ap_login]` to display the front-end login form.

## Registration Shortcode

Use `[ap_register]` to display a standalone registration form for new users. Signing up through this form automatically grants the “Free” membership level. After submitting the form a confirmation message appears and the user is redirected to their dashboard.


## Stripe Radar

To receive early fraud warnings you must enable the **early_fraud_warning.created** webhook event in your Stripe dashboard. Configure it to post to `/wp-json/artpulse/v1/stripe-webhook` on your site. When Radar flags a charge, the plugin logs the details and emails the site administrator.

## Dashboard Caching

Queries in the organization dashboard that previously loaded all related posts on every page view now utilize WordPress transients. Cached results are stored per organization for 15 minutes and cleared whenever related posts are saved. This reduces repetitive database lookups and improves page load times, especially for organizations with large numbers of posts.

## Database Indexes

On activation the plugin adds indexes to core meta tables if they don't already exist:

- **wp_usermeta**: `ap_usermeta_key_value` on `(meta_key, meta_value)`
- **wp_postmeta**: `ap_postmeta_key_value` on `(meta_key, meta_value)`

These indexes improve lookups for membership-related keys like `ap_organization_id`, `ap_membership_level` and `ap_membership_expires`.
