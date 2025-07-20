---
title: ArtPulse Codex: Reporting Snapshot Builder
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Reporting Snapshot Builder

Audience: Dev, Admin

Status: Ready

This document outlines the O1 sprint goal of creating quick reporting snapshots for grant documentation.

## REST Endpoints
- `GET /wp-json/artpulse/v1/reporting/snapshot?org_id={id}&period=YYYY-MM` – JSON summary.
- `GET /wp-json/artpulse/v1/reporting/snapshot.csv?org_id={id}&period=YYYY-MM` – Stream CSV export.
- `GET /wp-json/artpulse/v1/reporting/snapshot.pdf?org_id={id}&period=YYYY-MM` – Download PDF table via `Dompdf`.

## Dev Checklist
- `SnapshotBuilder` aggregates event metrics such as RSVPs, check-ins and revenue.
- `SnapshotBuilder::generate_pdf()` wraps `Dompdf` logic. When the
  `ArtPulse\Core\DocumentGenerator` helper is available the HTML table is
  passed through that class for consistent styling; otherwise `Dompdf` is used
  directly.
- `SnapshotBuilder::generate_csv()` streams rows with `fputcsv()`.
- Endpoints require `manage_options` capability before exporting.

See also: [Analytics Reporting Codex](guides/developer/analytics-reporting-codex.md), [reporting-exports.md](./reporting-exports.md)

## Implementation Details

`SnapshotBuilder::generate_pdf()` accepts a `title` and an array of `data`
pairs. It builds a simple two-column table in HTML and writes the resulting
PDF to the WordPress uploads directory. A sanitized slug derived from the title
becomes part of the file name. When the optional `DocumentGenerator` helper is
available, the HTML is passed through that class before `Dompdf` renders the
file. If `Dompdf` is missing the method returns an empty string.

### Example Payload

```php
$path = SnapshotBuilder::generate_pdf([
    'title' => 'April 2025 Snapshot',
    'data'  => [
        'Total Events' => 6,
        'RSVPs'        => 120,
        'Revenue'      => '$5000',
    ],
]);
```

### Permission Checks

All snapshot endpoints use a `permission_callback` that verifies
`current_user_can('manage_options')`. Unauthorized requests receive a
`403` response.

```bash
curl -H "X-WP-Nonce: <nonce>" \
  '/wp-json/artpulse/v1/reporting/snapshot.pdf?org_id=7&period=2025-04'
```

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
