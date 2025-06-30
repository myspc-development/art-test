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

## 2. Audience Demographics Visualization

If RSVP data includes fields like age or location, aggregate them for charts such as pie charts or maps. Only expose demographics when enough RSVP records exist to preserve privacy.

## 3. Event Performance Comparison

Summaries compare the selected event to previous events by the same artist. Metrics may include RSVP count, number of favorites and attendance percentage. Provide a quick “+12% vs average” style readout or mini bar charts.

## 4. Device & Platform Usage Stats

Track what devices visitors use when interacting with event pages or RSVPing.
Store the user agent string during RSVP submission and categorize it as
`desktop`, `mobile` or `tablet`.  A REST endpoint should expose aggregated
counts so the dashboard can display a bar chart or doughnut chart.

Example endpoint:

```
GET /wp-json/artpulse/v1/analytics/devices?event_id=123
```

The response includes percentage breakdowns by device type.

## 5. Attendance Loyalty Metrics

Measure how many unique attendees RSVP for multiple events held by the same
organization.  Query the RSVP table for recurring user IDs and return both the
absolute numbers and percentage of repeat attendees.

```
GET /wp-json/artpulse/v1/analytics/loyalty?org_id=42
```

## 6. Cross-Event KPI Trends

Allow artists or organizations to compare several events at once. Send a list of
event IDs and an optional date range to a REST endpoint which returns time series
data for RSVPs, revenue and attendance.

```
GET /wp-json/artpulse/v1/analytics/event-trends?event_ids=1,2,3&from=2025-01-01&to=2025-03-01
```

Render multiple lines on a Chart.js graph so trends can be easily compared.

## 7. Data Export & Scheduled Reports
Analytics can be downloaded as CSV files.  Each dashboard section should include
an **Export CSV** button that streams the underlying data after checking user
capabilities.  Optionally administrators can configure scheduled reports that
email a CSV or PDF summary daily, weekly or monthly.

## 8. Extensibility

Hooks like `artpulse_register_analytics_widget` allow themes or plugins to add widgets to the dashboard. When adding new metrics (e.g. revenue or social sharing), update this guide and keep the API responses backwards compatible.
