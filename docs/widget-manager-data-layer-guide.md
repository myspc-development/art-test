# Widget Manager Data Layer Guide

This document summarizes how dashboard layouts and visibility settings are stored and retrieved.

## Default Layouts
- Stored in the **`ap_dashboard_widget_config`** option.
- Each entry contains block pattern markup keyed by role: `member`, `artist`, `organization`.
- Updated via the block editor when an administrator saves layouts.

## Per‑User Layouts
- Saved in user meta keys:
  - `ap_dashboard_layout` — block markup for the user's layout.
  - `ap_widget_visibility` — visibility booleans by ID.
- When a user has no layout saved, the system falls back to their role's default.
- If neither user nor role layout exists, all registered widgets are shown.

## Saving Logic
1. Users drag widgets on their dashboard; positions are posted via REST.
2. Admins editing defaults trigger AJAX actions that update both the option and user meta when applicable.
3. Visibility toggles are stored alongside the layout so hidden widgets persist between sessions.

## Retrieval Flow
1. Load user meta for layout and visibility.
2. If empty, load role layout from `ap_dashboard_widget_config`.
3. Merge block markup with registered widget blocks so missing widgets are appended if needed.

See [User Dashboard Customization](./user-dashboard-customization.md) for additional context.
