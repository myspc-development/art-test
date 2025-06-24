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

Use `[ap_register]` to display a standalone registration form for new users. Signing up through this form automatically grants the “Free” membership level.


## Stripe Radar

To receive early fraud warnings you must enable the **early_fraud_warning.created** webhook event in your Stripe dashboard. Configure it to post to `/wp-json/artpulse/v1/stripe-webhook` on your site. When Radar flags a charge, the plugin logs the details and emails the site administrator.

## Integration Hooks

External systems may subscribe to custom actions fired by the plugin:

- `ap_user_registered` &ndash; triggered after a new user account is created.
- `ap_membership_upgraded` &ndash; fires when a user's membership level increases. Parameters: `$user_id`, `$new_level`.
- `ap_membership_downgraded` &ndash; fires when a membership is downgraded. Parameters: `$user_id`, `$new_level`.
- `ap_membership_expired` &ndash; triggered by the daily cron job when a membership lapses.

Example usage:

```php
add_action( 'ap_membership_upgraded', function ( $user_id, $level ) {
    wp_remote_post( 'https://example.com/webhook', [
        'body'    => [ 'user' => $user_id, 'level' => $level ],
    ] );
} );
```

These hooks can be used to initiate REST calls or other integrations.
