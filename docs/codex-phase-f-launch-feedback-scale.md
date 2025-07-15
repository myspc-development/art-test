# Codex: Phase F – Launch, Feedback & Scale

## Overview
This phase prepares ArtPulse for a wider audience through a public beta,
collects real‑world feedback, and ensures stability for future growth.

## Goals
- Real-world usage testing
- Community & partner onboarding
- Monitoring, performance, and rollout controls

## Public Beta Launch Workflow
- **Invite system** – issue temporary artist or gallery invite codes
- **Onboarding flow** – simple profile wizard for new users
- **Launch mode toggle** – hide unfinished features and show a “Beta” badge
- **Feature flags** – use `defined()` checks or JSON config for toggling chat, widgets and export functionality

## Feedback Collection Tools
- **Quick feedback widget** shown on the dashboard and event edit screens
- **Bug reporter** linking to GitHub Issues or an internal form
- **Net Promoter Score (NPS) survey** appears after the third event is published or attended

## Monitoring & Stability
- **PHP errors** captured via `WP_DEBUG_LOG`, Sentry or Loggly
- **JS bundle size** checked with webpack‑bundle‑analyzer
- **REST response times** logged to the `ap_api_logs` table for slow calls
- **Cron health** monitored so background tasks run on schedule

## Partner Launch Kits
- **Branding/Embed Kit** with widget instructions, logos and brand rules
- **Gallery onboarding PDF** describing how to list events, get verified and use analytics
- **Artist checklist** outlining preparations before a first show

## QA Checklist
- [ ] Login as artist, create event
- [ ] Follow curator, check feed
- [ ] Export org matrix
- [ ] Upvote an event

## Execution Plan
- **F1** – Public beta with limited user invites
- **F2** – Feedback integrated and final Codex updates
- **F3** – Gallery/city onboarding pilot with embeddables and analytics
- **F4** – Full release prep including docs, changelog and support coverage
