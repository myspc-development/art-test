---
title: ArtPulse Codex: Reporting & Exports
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Reporting & Exports

Use these tools to capture real attendance numbers and generate grant-ready logs.

## 1. Admin Dashboards
- View turnout metrics for each event in the dashboard.
- Engagement charts can be exported as images or CSV.

## 2. Attendance Logs
- Combine RSVPs with check-ins to measure actual visitors.
- Logs include optional feedback scores from curators or the public.

## 3. Export Formats
- CSV download for spreadsheets.
- PDF snapshot for monthly grant reports.
- Scheduled reports email these files automatically each week or month.
- Manual snapshots can be generated under **ArtPulse → Settings → Snapshots**.

## Developer Checklist
- `/analytics` endpoints expose turnout and engagement data.
- CSV export uses `fputcsv()` to stream records.
- PDF export relies on `Dompdf` with header and footer templates.

💬 Found something outdated? Submit Feedback
