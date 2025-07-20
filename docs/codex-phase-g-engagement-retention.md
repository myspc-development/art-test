---
title: Codex: Phase G – Engagement & Retention
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# Codex: Phase G – Engagement & Retention

## Overview
This phase introduces mechanisms that encourage long-term usage and loyalty across artists, galleries and attendees. Features include digest emails, badges, referral codes, a unified inbox and contextual creator tools.

## Goals
- Encourage weekly return visits
- Reward active creators and organizations
- Leverage user networks through invites
- Centralize platform notifications and actions

## G1. Weekly Personalized Digest Email
Send a weekly email with nearby or followed events.

### Implementation
- Opt-in user meta `ap_digest_frequency`
- Daily cron `ap_daily_digest` checks frequency
- Match events by follows, tags and region
- Email template listing title, image and RSVP button

## G2. Badges & Activity Milestones
Award badges for high engagement.

### Badge Examples
- **Streak: 3 in 30** – 3+ events in 30 days
- **100 RSVPs Club** – Event reaches 100 RSVPs
- **Studio Host** – Hosts a virtual studio visit

Badges store in `earned_badges` user meta and render on profiles.

## G3. Referrals & Invite Tracking
Track invite codes so creators can grow the network.

### Elements
- `ap_referrals` table storing invite codes
- `POST /artpulse/v1/referral/redeem` endpoint
- Referral link shown on creator profiles
- Bonus “Connector” badge after 3 referrals

## G4. Inbox & Announcements
Central hub for mentions, follows and admin messages.

### Components
- `ap_notifications` table
- REST: `GET /artpulse/v1/inbox`, `POST /artpulse/v1/inbox/read`, `POST /artpulse/v1/inbox/unread`
- `InboxPanel.jsx` lists messages with links to events or profiles

## G5. Creator Toolkit
Dashboard panel with contextual tips.

### Display Rules
Shown when creators have
- Fewer than 10 RSVPs
- No donation link
- No badges yet

Offers actions like featuring an event or embedding a widget.

## Developer Checklist
- [x] Weekly digest email + opt-in
- [x] Badge rules and display
- [x] Referral codes and tracking
- [x] Notifications REST and inbox UI
- [x] Creator tips component

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
