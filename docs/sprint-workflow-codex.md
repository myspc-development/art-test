---
title: ArtPulse Codex: Sprint Workflow
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Sprint Workflow

Before starting **Sprint 2**, each developer must select their assigned feature(s) from the [`Sprint_Backlog`](./Sprint_Backlog.md). For every selected item cross‑check the [`Feature_Completion_Tracker`](./Feature_Completion_Tracker.md) so all subcomponents, requirements and blockers are understood.

If a feature is marked **Incomplete** or is missing from the tracker:
1. Add the feature to the tracker if it is not already listed.
2. Fill in the Verification Checklist entries as you implement the feature.
3. Mark status updates after unit tests, integrations and QA sign‑off.

Use the `Upgrade_Implementation_Guide` whenever technical debt, refactoring or dependency changes are involved.

## Daily Update Routine
The lead developer for each feature is responsible for keeping both the `Sprint_Backlog` and the `Feature_Completion_Tracker` current. Update the status columns at the end of every day or immediately after a major commit.

## Bonus Guidance for Developers
- Tag pull requests with `[Feature]`, `[Fix]` or `[Refactor]` and include the feature ID (`F-xxx`) and sprint ID (`SB-xxx`).
- Update both the `Sprint_Backlog` and `Feature Completion Tracker` at the end of each day or after significant progress.
- Sync with QA and UI/UX before marking a feature as **Complete**.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
