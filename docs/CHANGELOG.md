# Changelog

## 1.3.15
- Added admin pages for managing ticket tiers and membership levels.
- Introduced REST endpoints for ticket tier CRUD and membership level settings.
- WooCommerce hooks now register automatically when enabled.

## 1.3.14
- Documentation updates and task status refinements.

## 1.3.13
- Added community analytics dashboard with REST endpoints for messages, comments and forums.

## 1.3.9
- Introduced Social Auto-Posting with Facebook, Instagram, Twitter/X and Pinterest support.
- New settings tab allows per-post-type toggles and API credentials.
- Removed unused `src/Community/installer.php` in favor of `FavoritesManager::install_favorites_table()`.

## 1.3.10
- Icons from `lucide-react` are now bundled with `ap-dashboard.js` using ES
  module imports, eliminating the need for a global `lucideReact` script.
- The installed plugin version is saved in `ap_plugin_version`. When this value
  differs from the plugin header version a modal with release notes is shown to
  administrators until dismissed.

## 1.3.11
- Five image slots with drag-and-drop ordering.
- Organization event form now supports individual image uploads.

## 1.3.12
- Introduced OpenAI-powered auto tagging when posts are saved.
- Added `/bio-summary/{id}` REST endpoint with GPT summaries for artist bios.
- New `bio-summary.js` renders summaries on artist pages.

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

