# ArtPulse Style Guide

Legacy styling is defined in `assets/css/ap-style.css` and compiled from
`assets/scss/`. New features should rely on Tailwind utility classes instead.
As Tailwind equivalents are implemented this stylesheet will be removed.

Use the following standard classes for all dashboards, widgets and forms:

- `.ap-dashboard` – wrapper for dashboard layouts
- `.ap-widget` – card or widget container
- `.ap-widget-header` – optional header section inside a widget
- `.ap-widget-body` – body content of a widget
- `.dashboard-nav` – dashboard navigation links
- `.ap-form-container` – form wrapper
- `.ap-form-label` – form labels
- `.ap-input` – form inputs and textareas
- `.ap-form-button` – buttons within forms or dashboards

No other CSS files should be loaded or referenced. Any new shortcodes or features
must use these classes so the UI remains consistent.
