---
title: 🧭 Organizer Dashboard Roadmap
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# 🧭 Organizer Dashboard Roadmap

---

## PHASE 1: Setup & Permissions

### ✅ 1. Organization Profiles
 - [x] Custom post type updates for CRM fields
 - [x] REST endpoints secured for managers only

### ✅ 2. Dashboard Skeleton
 - [x] Sidebar menu with tabs for CRM, Donations, Analytics
 - [x] Basic widgets container using existing registry

---

## PHASE 2: CRM & Contacts
 - [x] `CrmContactsWidget.php` lists member interactions
 - [x] `/crm/contacts` REST route with filtering
 - [x] Tagging interface for notes and follow ups

---

## PHASE 3: Donor & Payment Tools
 - [x] `DonationsSummaryWidget.php` displays recent gifts
 - [x] `/donations/overview` REST route
 - [x] Export to CSV for grants reporting

---

## PHASE 4: Analytics & Reports
 - [x] Attendance trends chart
 - [x] Ticket sales and conversion metrics
 - [x] Custom report builder with saved filters

---

## PHASE 5: QA and Documentation
 - [x] PHPUnit coverage for new REST endpoints
 - [x] Widget docs and admin guide updates

## Completion Plan

1. Establish organization permissions and base dashboard widgets.
2. Integrate CRM contact management in Phase 2.
3. Add donor tools and exports in Phase 3.
4. Build analytics and reporting screens during Phase 4.
5. Finalize tests and documentation to wrap up Phase 5.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
