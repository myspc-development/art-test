# Changelog

## 1.3.9
- Introduced Social Auto-Posting with Facebook, Instagram, Twitter/X and Pinterest support.
- New settings tab allows per-post-type toggles and API credentials.
- Removed unused `src/Community/installer.php` in favor of `FavoritesManager::install_favorites_table()`.

## 1.3.8
- Added EmailTemplateManager and customizable template field.
- Ticket purchases now send a PDF ticket attachment.
- DocumentGenerator utility introduced for simple PDF generation.

## 1.3.7
- Adjusted table creation statements to use explicit PRIMARY KEY clause.
- Added follower REST endpoints and `[ap_my_follows]` shortcode.
- Introduced engagement feed and drag-and-drop dashboard widgets with dark mode.
- Cleared scheduled message cron jobs on plugin deactivation.

## 1.3.6
- Added `ap_artwork_event_links` table for linking artworks to events.
- Updated installer and uninstaller hooks to manage the new table.
- Introduced `ArtworkEventLinkManager` with unit tests.

## 1.3.5
- Introduced multi‑image event galleries with Swiper slider support.
- Added full RSVP system including waitlists, REST endpoints and analytics.
- Implemented favorites, engagement dashboards and inline event editing.
- Extended event filtering with a FullCalendar view and reusable cards.
- Added profile link requests, privacy options and OAuth login integration.

