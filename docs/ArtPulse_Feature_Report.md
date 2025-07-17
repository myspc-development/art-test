# ArtPulse Plugin – Features & Functions Report

Version: 1.3.14
Platform: WordPress (React + PHP REST)
Use Case: Manage creative events, memberships and engagement for artists and organizations.

## 🔧 Core Functional Modules

1. **Event Management & RSVPs**
   - Event directories, calendars and map views
   - RSVP and favorite tracking with real‑time updates
   - Front‑end submission forms for organizations
   - Support for recurring events and co‑host artists

2. **Membership & Ticketing**
   - WooCommerce integration for selling memberships
   - Shortcodes for login, registration and membership purchase
   - Membership overrides and automatic expiration of past events

3. **Dashboards & Analytics**
   - Member and organization dashboards with charts powered by Chart.js
   - Export RSVPs to CSV and mark attendance
   - Login heatmap and engagement trends

4. **Messaging & Notifications**
   - Attendee messaging and bulk email tools
   - Optional email/SMS reminders and social auto‑posting
   - Service worker support for push notifications and offline caching

5. **CRM & Donor Tools**
   - Contacts tagged automatically when users RSVP, donate or follow
   - Dashboard CRM tab with CSV export
   - Donation and payout integrations via Stripe

6. **Widgets & Shortcodes**
   - Embeddable event widgets and sliders
   - AJAX search and filter forms
   - Automatic page generation for selected shortcodes

7. **Privacy & Security**
   - Nonce validation and REST permission callbacks
   - Two‑Factor login and role‑based capabilities
   - Data retention controls and database indexes

## 🔮 Planned & Enhanced Features

| Area | Feature | Milestone |
|------|---------|----------|
| AI | Auto‑Tagger improvements with multilingual model | Sprint 5 |
| Membership | Ticketing & membership enhancements | Sprint 6 |
| Analytics | Dashboard widget builder | Sprint 7 |
| QA | Automated tests & CI/CD workflow | Phase 5 |

## 🧱 Architecture

| Layer | Stack |
|-------|-------|
| Frontend | React + Tailwind CSS |
| Backend | PHP + WordPress REST API |
| AI | OpenAI API (configurable) |
| Storage | Custom post types: `artpulse_event`, `artpulse_artist`, etc. |
| CI/CD | GitHub Actions + Plugin Installer |

## 📁 Documentation Map

| Type | Location |
|------|---------|
| Features | `/docs/` |
| Developer Setup | `/docs/dev/` |
| REST API Spec | `/docs/openapi.yaml` |
| Milestones | `/docs/roadmap/` |
| Assistant Guide | `ASSISTANT_INSTRUCTIONS.md` |
