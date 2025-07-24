# Portfolio Sync

The plugin can mirror selected custom post types into the Salient theme's Portfolio CPT. Event, artist and organization posts are supported.

## Mapping
- **Title & Content** – copied directly to the portfolio post.
- **Featured Image** – uses the submission gallery image or the post thumbnail.
- **Taxonomies** – `portfolio_category` and `portfolio_tag` map to Salient's `project-category` and `project-tag`.
- **Project Category Labels** – each synced type also receives a label term ("Event", "Artist", "Organization" by default) in `project-category`.
- **Meta** – the portfolio stores `_ap_source_post` and `_ap_source_type` to reference the origin post.
- **Relationships** – event portfolios reference artist and organization portfolios via `_ap_related_artists` and `_ap_related_org` meta.

## Bulk Migration
Use **Portfolio Sync → Migrate Legacy Portfolios** or run `wp ap migrate-portfolio` to generate portfolio posts for existing content.

## Settings
Administrators can select which CPTs sync and customize the category labels under **Portfolio Sync → Sync Settings**.
