# ArtPulse Style Guide

Legacy styling is defined in `assets/css/ap-style.css` and compiled from
`assets/scss/`. New layouts rely on the Salient theme and WPBakery
components. As modules are modernized this stylesheet will eventually be
retired.

Use the following standard classes for all dashboards, widgets and forms:

- `.ap-dashboard` – wrapper for dashboard layouts
- `.ap-widget` – card or widget container
- `.ap-widget-header` – optional header section inside a widget
- `.ap-widget-body` – body content of a widget
- `.ap-widget-card` – standard wrapper for dashboard widgets
- `.ap-widget-icon` – small icon displayed in the header
- `.ap-widget-title` – widget title element
- `.ap-widget-controls` – wrapper for toggle/remove buttons
- `.ap-widget-content` – main widget output area
- `.ap-widget-tag` – visual tag or category label
- `.dashboard-nav` – dashboard navigation links
- `.ap-form-container` – form wrapper
- `.ap-form-label` – form labels
- `.ap-input` – form inputs and textareas
- `.ap-form-button` – buttons within forms or dashboards

No other CSS files should be loaded or referenced. Any new shortcodes or features
must use these classes so the UI remains consistent.

Layouts should be constructed using WPBakery row and column elements. The
Salient theme provides additional utility classes which can be combined with
the plugin-specific classes above.
