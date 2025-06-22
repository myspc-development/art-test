# ArtPulse Admin Guide

Welcome to ArtPulse! This short guide outlines the main tools available for site administrators.

## Installation & Setup
1. Install the plugin and activate it from your WordPress dashboard.
2. Configure prices, Stripe keys and other options under **ArtPulse → Settings**.

## Managing Members
- Use the **Members** submenu to search, filter and export member data.
- Approve or reject content under **Pending Submissions** when moderation is required.

## Engagement & Analytics
- Visit **Engagement Dashboard** for charts showing logins and uploads.
- Review daily and weekly activity to monitor community growth.

## Organization Tools
- Organizations have their own dashboard with linked artists, artworks and events.
- Billing history and membership level are visible on each organization profile.
- Use `[ap_org_profile_edit]` to let organizations update their profile details.

## Shortcodes
Use these codes on any page to display forms or dashboards:

- `[ap_org_dashboard]` – organization dashboard
- `[ap_my_events]` – list events submitted by the user
- `[ap_portfolio_builder]` – manage personal portfolio items
- `[ap_submit_event]` – event submission form
- `[ap_filtered_list]` – output a filtered list of posts
- `[ap_profile_edit]` – user profile editing form
- `[ap_org_profile_edit]` – edit an organization profile
- `[ap_user_profile]` – show a user profile
- `[ap_submission_form]` – generic submission form
- `[ap_submit_organization]` – organization submission form
- `[ap_edit_event]` – edit an existing event
- `[ap_notifications]` – display user notifications
- `[ap_membership_account]` – membership account page
- `[ap_my_follows]` – list followed items
- `[ap_membership_purchase]` – link to purchase membership
- `[ap_events]` – grid of events
- `[ap_artists]` – grid of artists
- `[ap_artworks]` – grid of artworks
- `[ap_organizations]` – grid of organizations
- `[ap_user_dashboard]` – user dashboard and content
- `[ap_directory]` – filterable directory

### `[ap_directory]`
The directory shortcode displays items grouped alphabetically A–Z. Each letter section lists the matching results beneath it.

Example usage:

```
[ap_directory type="artist" limit="20"]
```

You can also filter results by event type, city or region using additional attributes.

## User Dashboard Calendar
The `[ap_user_dashboard]` shortcode now lists local events based on the user's
stored location. You can set or update this location in the profile editor
via `[ap_profile_edit]`. When no location is saved, the calendar falls back to
showing favorited events instead. Change which events display by updating your
location or adding/removing favorites, or extend the calendar behaviour further
through JavaScript.

## Troubleshooting
- Check the **System Status** section on the Settings page to see the latest webhook events.
- Clear the log or simulate a webhook event if needed for testing.

