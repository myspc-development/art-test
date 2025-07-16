# Shortcodes Reference

This table was auto-generated from `ShortcodePages::get_shortcode_map()`.

| Shortcode | Attributes | Example |
|-----------|------------|---------|
| `[ap_login]` | — | [ap_login] |
| `[ap_register]` | — | [ap_register] |
| `[ap_logout]` | — | [ap_logout] |
| `[ap_user_dashboard]` | — | [ap_user_dashboard] |
| `[ap_profile_edit]` | — | [ap_profile_edit] |
| `[ap_submit_organization]` | — | [ap_submit_organization] |
| `[ap_submit_artist]` | — | [ap_submit_artist] |
| `[ap_org_dashboard]` | — | [ap_org_dashboard] |
| `[ap_artist_dashboard]` | — | [ap_artist_dashboard] |
| `[ap_submit_event]` | — | [ap_submit_event] |
| `[ap_portfolio_builder]` | — | [ap_portfolio_builder] |
| `[ap_my_events]` | — | [ap_my_events] |
| `[ap_my_follows]` | — | [ap_my_follows] |
| `[ap_notifications]` | — | [ap_notifications] |
| `[ap_membership_account]` | — | [ap_membership_account] |
| `[ap_membership_purchase]` | — | [ap_membership_purchase] |
| `[ap_user_profile]` | — | [ap_user_profile] |
| `[ap_events]` | — | [ap_events] |
| `[ap_event_calendar]` | — | [ap_event_calendar] |
| `[ap_events_slider]` | — | [ap_events_slider] |
| `[ap_event_comments]` | — | [ap_event_comments] |
| `[ap_event_filter]` | — | [ap_event_filter] |
| `[ap_event_listing]` | — | [ap_event_listing] |
| `[ap_event_map]` | — | [ap_event_map] |
| `[ap_artists]` | — | [ap_artists] |
| `[ap_artworks]` | — | [ap_artworks] |
| `[ap_event_directory]` | — | [ap_event_directory] |
| `[ap_artist_directory]` | — | [ap_artist_directory] |
| `[ap_artwork_directory]` | — | [ap_artwork_directory] |
| `[ap_org_directory]` | — | [ap_org_directory] |
| `[ap_org_event_form]` | — | [ap_org_event_form] |
| `[ap_directory]` | — | [ap_directory] |
| `[ap_organizations]` | — | [ap_organizations] |
| `[ap_submission_form]` | — | [ap_submission_form] |
| `[ap_edit_event]` | — | [ap_edit_event] |
| `[ap_filtered_list]` | — | [ap_filtered_list] |
| `[ap_org_profile_edit]` | — | [ap_org_profile_edit] |
| `[ap_org_profile]` | — | [ap_org_profile] |
| `[ap_artist_profile_form]` | — | [ap_artist_profile_form] |
| `[ap_collection]` | — | [ap_collection] |
| `[ap_collections]` | — | [ap_collections] |
| `[ap_competition_dashboard]` | — | [ap_competition_dashboard] |
| `[ap_event_chat]` | — | [ap_event_chat] |
| `[ap_favorite_portfolio]` | category, limit, sort, page | [ap_favorite_portfolio limit="12"] |
| `[ap_favorites_analytics]` | type, user_id, admin_only, roles | [ap_favorites_analytics type="detailed"] |
| `[ap_messages]` | — | [ap_messages] |
| `[ap_message_form]` | — | [ap_message_form] |
| `[ap_org_rsvp_dashboard]` | — | [ap_org_rsvp_dashboard] |
| `[ap_payouts]` | — | [ap_payouts] |
| `[ap_recommendations]` | — | [ap_recommendations] |
| `[ap_render_ui]` | — | [ap_render_ui] |
| `[ap_spotlights]` | — | [ap_spotlights] |

## `[ap_org_rsvp_dashboard]`

This shortcode displays a lightweight RSVP management dashboard for event
organizers. When added to a page it lists every event created by the
logged‑in user that has RSVP tracking enabled (`ap_event_requires_rsvp` meta
set to `1`).

### Expected Markup

For each event the shortcode outputs an `<h3>` heading with the event title
followed by an unordered list of attendee names and email addresses. If an
event has no RSVPs the list contains a single “No RSVPs yet” item.

### Required Capabilities

Visitors must be logged in to view this dashboard. Only events authored by
the current user are queried, so organization admins or artists with the
ability to publish events will see results.

### Example

```no-highlight
[ap_org_rsvp_dashboard]
```

### Filters and Hooks

There are no dedicated filters for this shortcode. Developers may rely on
standard WordPress hooks such as `pre_get_posts` to adjust the event query
or modify output via `the_content`.
