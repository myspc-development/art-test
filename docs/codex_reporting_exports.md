# ArtPulse Codex: Reporting Snapshot Builder

This document outlines the O1 sprint goal of creating quick reporting snapshots for grant documentation.

## REST Endpoints
- `GET /wp-json/artpulse/v1/reporting/snapshot?org_id={id}&period=YYYY-MM` – JSON summary.
- `GET /wp-json/artpulse/v1/reporting/snapshot.csv?org_id={id}&period=YYYY-MM` – Stream CSV export.
- `GET /wp-json/artpulse/v1/reporting/snapshot.pdf?org_id={id}&period=YYYY-MM` – Download PDF table via `Dompdf`.

## Developer Checklist
- `SnapshotBuilder` aggregates event metrics such as RSVPs, check-ins and revenue.
- `SnapshotBuilder::generate_pdf()` uses `DocumentGenerator` when available.
- `SnapshotBuilder::generate_csv()` streams rows with `fputcsv()`.
- Endpoints require `manage_options` capability before exporting.

See also: [analytics-reporting-codex.md](./analytics-reporting-codex.md), [reporting-exports.md](./reporting-exports.md)
