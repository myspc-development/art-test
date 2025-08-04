---
title: `ap_user_dashboard` Shortcode
category: shortcodes
role: developer
last_updated: 2025-07-28
status: draft
---

## `ap_user_dashboard`

Render the logged-in user's dashboard with widgets tailored to their role.

```wp-shortcode
[ap_user_dashboard]
```

### Embedding individual widgets

Use the `[ap_widget]` shortcode to surface a single dashboard widget on any
page. The optional `role` attribute limits rendering to specific roles and is
checked against the widget's permitted roles from the registry.

```wp-shortcode
[ap_widget id="membership" role="member"]
[ap_widget id="artist_revenue_summary" role="artist,organization"]
```

Widgets appear only when the current user's role matches both the shortcode's
`role` attribute and the roles defined for that widget.
