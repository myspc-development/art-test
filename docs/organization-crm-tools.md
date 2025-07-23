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

`assets/js/widgets/AudienceCRMWidget.jsx` provides a React component that fetches contacts from the REST endpoint `/artpulse/v1/org/<id>/audience`. It lists names or emails of contacts tagged by the CRM system. Add the widget through the dashboard editor for organization roles.

## REST API Endpoints

`src/Rest/OrgCrmController.php` exposes `GET /org/<id>/donors` and `GET /org/<id>/audience` routes for use in custom tooling or exports.
