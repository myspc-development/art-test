# ArtPulse Codex: Analytics & Insights

This guide describes how to present event performance metrics and audience engagement data to artists and organizations. The modules are optional and focus on visual dashboards powered by REST endpoints.

## 1. Event Analytics Dashboard

Display event‑level statistics with charts.

* **Total RSVPs** and **Total Favorites**
* **Attendance rate** if the site tracks check‑ins
* **Trends over time** via line or bar charts

Endpoints should accept an `event_id` and date range. Data can be cached using transients for speed.

Example endpoint:

```
GET /wp-json/artpulse/v1/analytics?event_id=123&from=2024-01-01&to=2024-02-01
```

## 2. Audience Demographics (Optional)

If RSVP data includes fields like age or location, aggregate them for charts such as pie charts or maps. Only expose demographics when enough RSVP records exist to preserve privacy.

## 3. Event Performance Comparison

Summaries compare the selected event to previous events by the same artist. Metrics may include RSVP count, number of favorites and attendance percentage. Provide a quick “+12% vs average” style readout or mini bar charts.

## 4. Exporting Data

Artists can download their analytics as CSV files. The export endpoint should check capabilities and stream results via `fputcsv()`.

## Extensibility

Hooks like `artpulse_register_analytics_widget` allow themes or plugins to add widgets to the dashboard. When adding new metrics (e.g. revenue or social sharing), update this guide and keep the API responses backwards compatible.
