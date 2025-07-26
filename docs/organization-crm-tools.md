---
title: Organization CRM Tools
category: developer
role: developer
last_updated: 2025-07-23
status: complete
---

# Organization CRM Tools

This guide describes the donor and audience tracking widgets for organization administrators.

## Donor Activity Widget

`widgets/DonorActivityWidget.php` registers a dashboard widget that lists all donations associated with the admin's organization. Filters allow narrowing by date range. Donation data is pulled from the `ap_donations` table via `DonationModel::query()`.

## Audience CRM Widget

`assets/js/widgets/AudienceCRMWidget.jsx` previously fetched contacts from the
REST endpoint `/artpulse/v1/org/<id>/audience`. These routes were removed along
with the `OrgCrmController`.

## REST API Endpoints (Removed)

The controller `src/Rest/OrgCrmController.php` and its donor and audience
routes were removed in the 2025 cleanup.
