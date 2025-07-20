---
title: Codex: Phase M – Creator & Curator Module
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# Codex: Phase M – Creator & Curator Module

## Overview
This phase unifies tools for artists, curators and collectives so ArtPulse becomes a creator-driven ecosystem rather than only a listings portal. The module spans four sprints and introduces collaboration features, curated threads, curator profiles and dashboard utilities.

## Module Goals
| Role | Capabilities Introduced |
| --- | --- |
| Artists | Claim, co-list, and showcase shows they’re part of |
| Collectives | Form public pages and project hubs |
| Curators | Create thematic threads, track RSVPs, publish annotated bundles |
| Admins | Unlock dashboard tools to view collaboration notes and network reach |

## Features by Sprint

### Sprint M1 – Artist Collaboration Tools
- **Co-listed Shows** – add multiple artists to one event
- **Studio & Collective Pages** – auto-pull shared shows into group landing pages
- **“Shows I’m In” Feed** – auto-filled tab on each artist profile
- **Shared Tag Pools** – merge style, region and medium metadata

### Sprint M2 – Curated Threads & Bundles
- **Curator Threads** – time-limited themes with editorial intro
- **Event Bundles** – mini collections (3–10 shows) sharable with unique URL
- **Thread Publisher UI** – drag-to-add interface with intro text block
- **Showcase Format** – `/thread/queer-sound-week-2025` → clean reader view

### Sprint M3 – Curator Profiles & Discovery
- **Curator Bios** – public profiles with tags and focus areas
- **Showcase Grid** – view all threads and bundles a curator published
- **Collaborator Feed** – “Often collaborates with” section
- **Invite as Curator** – organizations can tag any user as invited guest curator

### Sprint M4 – Curator Dashboard Tools
- **RSVP Lists for Curated Shows** – view attendees and interest level per show
- **Draft & Schedule Threads** – save upcoming bundles without publishing
- **Private Notes** – curator-only notes per event for later follow-up
- **Share to Digest** – promote a thread or bundle in user digests

## Collective & Curator Pages
| Page Type | URL Format | Includes |
| --- | --- | --- |
| Artist Profile | `/artist/samira-bello` | Bio, upcoming shows, “Also exhibiting with” |
| Studio Group | `/group/red-door-studio` | Member list, location, joint events |
| Curator Thread | `/thread/queer-sound-week` | Curator intro, 4–8 events, optional commentary |
| Curator Profile | `/curator/elisandre` | Bio, past threads, bundles, specialties |

## Related UI Upgrades
- Event page shows “Featuring: [3 artists]” with profile links
- Search filter adds “Curated by [X]” and “Part of [Thread]”
- Homepage highlights featured thread or spotlight bundle
- Profile tools split into **Artist: My Shows** and **Curator: My Threads**

## QA Summary
| Test | Result |
| --- | --- |
| Artist is added to an event → event appears on their profile | ✅ |
| Group page shows all members’ events | ✅ |
| Curator thread shows custom intro and event bundle | ✅ |
| RSVP logs appear on dashboard for curator thread | ✅ |

## Codex Docs to Update
| Doc | Contents |
| --- | --- |
| `artist-profiles.md` | Co-listing, “My Shows” |
| `collective-pages.md` | How to create/manage group pages |
| `curator-tools.md` | Bundles, RSVPs, private notes |
| `thread-formatting.md` | How to curate, schedule and publish threads |
| `creator-network.md` | Overview of artist–curator–org connections |

## Suggested Public Copy
"ArtPulse supports artist-driven and curator-powered programming. You can co-list a show, create studio groups, and publish your own curated threads. Whether you’re organizing an art crawl or documenting a scene — you now have the tools."

## Next Extension Options
| Sprint | Add-On |
| --- | --- |
| M5 | Curator Commissions / Call for Submissions |
| M6 | Artist Reels / Embedded Studio Visit Videos |
| M7 | Reputation Scores & “Trusted Curator” badges |
| M8 | Creator-to-Creator Messaging (opt-in, secure) |

💬 Found something outdated? Submit Feedback
