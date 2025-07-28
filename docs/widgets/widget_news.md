---
title: News
category: widgets
role: developer
last_updated: 2025-07-24
status: complete
---

# News

**Widget ID:** `widget_news`

**Roles:** member, artist

## Description
Latest updates from ArtPulse.

The News widget pulls posts from the `/wp-json/wp/v2/posts` endpoint and renders the latest articles. By default the five most recent posts with the `news` tag are displayed.

### Customization
- Change the tag or number of posts using the block attributes `tag` and `posts_per_page`.
- Editors can reorder or hide the widget from the Dashboard Builder if news is not relevant to a particular role.

