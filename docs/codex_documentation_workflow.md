# Codex Documentation Workflow

Audience: Dev, PM

## Overview
These instructions guide the development team when auditing and unifying the Creator & Growth Codex documentation. Follow this process before each milestone to keep the knowledge base usable and sprint-ready.

## Document Review & Merge Checklist
- Search the repository for all `codex_*.md` files within the `docs/` directory or other folders.
- Build an index noting which codex file covers which sprint or module.
- Merge or remove any overlapping sections across files. Example: combine duplicate details in `codex_growth_stack.md` and `embed-widgets.md`.
- Verify that REST routes, UI names and filter options match the current plugin implementation.
- Each document must specify its primary audience: developer, PM, partner or artist.
- Ensure filenames follow the pattern `codex_[module-name].md`.
- Add `[🚧 Sprint Needed]` for sections that reference future work.
- Insert `<!-- REVIEW: unclear -->` comments where logic or requirements need clarification.

## Editing Guidelines
- Use tables to show feature comparisons, access roles and sprint breakdowns.
- Keep heading hierarchy consistent: `##` for primary sections, `###` for subsections and lists beneath.
- Wrap REST or API calls in backticks or fenced code blocks.
- Emoji may appear in lists or tables but never in headings.
- Include a `## Dev Checklist` section in every module document with tasks or acceptance criteria.
- Conclude with a "See also:" list of related codex files.

## Suggested Next Sprint Options
After cleanup, evaluate readiness of these groups:

| Sprint Block | Goal | Review Scope |
|--------------|------|--------------|
| **N1–N3** | Institution/Education Toolkit | Check RSVP tracking and class visit specs |
| **M5–M7** | Creator Reputation & Commissioning | Confirm access control and public vs private views |
| **K5–K6** | Mobile Crawl Night Add-ons | Verify check-ins and route saving integration |
| **O1–O2** | Metrics & Export Engine | Ensure event logs support analytics and CSV exports |

### Recommended Focus Choices
- **N1: Class Visit Tracking** – Extends existing check-in logic for schools.
- **M5: Curator Call for Submissions** – Builds upon curator tools for open curation.
- **K5: Crawl Mode MVP** – Connects nearby map, QR flows and field logging.
- **O1: Reporting Snapshot Builder** – Provides admins quick grant reporting.

## Latest Cleanup Summary
This summary lists documentation updates completed during the latest audit.

### Merged Files
- `codex_growth_stack.md` merged into `growth-stack-overview.md`
- `codex_basic_widgets.md` merged into `embed-widgets.md`
- `codex_permissions_overview.md` merged into `multi-org-roles-permissions.md`

### Renamed Documents
- `codex-creator-tools.md` → `codex-phase-m-creator-curator-codex.md`
- `mobile-pwa-guide.md` → `mobile-artwalk.md`

## Optional Output Deliverables
Create these summaries when the audit is complete:
- [`codex_index.md`](./codex_index.md) listing modules, sprint status and intended user.
- [`codex_sprint_readiness.md`](./codex_sprint_readiness.md) marking which sprints are spec-complete or blocked.

