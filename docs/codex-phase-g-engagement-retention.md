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
- Opt-in user meta `digest_enabled`
- Weekly cron `ap_send_weekly_digest`
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
- REST: `GET /artpulse/v1/inbox`, `POST /artpulse/v1/inbox/mark-read`
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
- [ ] Weekly digest email + opt-in
- [ ] Badge rules and display
- [ ] Referral codes and tracking
- [ ] Notifications REST and inbox UI
- [ ] Creator tips component
