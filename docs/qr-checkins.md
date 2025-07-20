---
title: ArtPulse Codex: QR Check-Ins & Attendance
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: QR Check-Ins & Attendance

This guide explains how to generate event QR codes and track real-world turnout.

## 1. QR Generator
- Create check-in links like `https://artpulse.io/checkin?event=123`.
- Print or share the code so visitors can scan on arrival.

## 2. Public Scan & Confirm
- No login required for guests to check in.
- The confirmation screen thanks them and records the visit.

## 3. Attendance Logs
- Hosts view check-ins matched against RSVPs.
- Data is stored per event for export or reporting.

## 4. CSV Export
- Download visit records ready for donors or grant reports.
- Include timestamp, user (if known) and any custom fields.

## Developer Checklist
- REST `/checkin` route validates the `event` parameter.
- Logs stored in `ap_event_checkins` with `event_id`, `user_id` and timestamp.
- Export handler streams CSV without loading all rows into memory.

💬 Found something outdated? Submit Feedback
