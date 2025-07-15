# Codex: Phase A – Core Infrastructure & Foundation

## Overview
This phase lays the groundwork for ArtPulse by implementing essential systems for access control, event discovery and external integrations. It combines the early sprints that enable mapping and Q&A, multi‑organization role management and webhook connectivity.

## Goals
- Provide location aware event listings with discussion threads.
- Support users that manage multiple organizations with scoped permissions.
- Deliver event data to partner systems via secure webhooks.

## Features by Sprint
### Sprint 1 – Mapping & Q&A
- REST filter `/artpulse/v1/events?lat={lat}&lng={lng}&within_km={km}`.
- `qa_thread` custom post type with REST endpoints.
- Rankings table `ap_event_rankings` for event scores.
- Map widget and Q&A components on event pages.

### Sprint H2 – Multi-org Roles & Permissions
- `ap_org_roles` table mapping users to orgs and roles.
- REST routes for listing and assigning roles.
- Dashboard context switcher and per‑org invites.

### Sprint J1 – Webhook Sync Framework
- `ap_webhooks` table storing subscriptions.
- REST API to create, update and remove webhooks.
- `ap_trigger_webhooks()` helper with HMAC signatures.
- Delivery logs to track response codes.

## Developer Checklist
- [ ] Map and Q&A endpoints return expected data.
- [ ] Multi‑org assignments persisted in `ap_org_roles`.
- [ ] Webhook payloads verified with partner tools.
