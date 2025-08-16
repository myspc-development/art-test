---
title: `ap_submit_organization` Shortcode
category: shortcodes
role: developer
last_updated: 2025-08-16
status: draft
---

## `ap_submit_organization`

Render a basic organization submission form. The form collects the
organization name along with optional country and city details. Up to
five images may be uploaded.

Submitted values are stored as a pending `artpulse_org` post. The
`country` and `city` fields are saved to post meta using the keys
`ap_org_country` and `ap_org_city`.

```
[ap_submit_organization]
```

The shortcode accepts no attributes.
