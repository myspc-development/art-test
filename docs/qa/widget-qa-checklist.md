---
title: Widget QA Checklist
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Widget QA Checklist

Combine these verification steps when testing the widget manager and editor.

## Verification Steps
- [ ] Widget registry updated with role metadata
- [ ] Dashboard layout endpoints registered
- [ ] Admin editor loads roles and widgets via REST
- [ ] Dashboard container wired to widget registry
- [ ] Matrix loads from config and allows role toggling
- [ ] Changes persist to backend correctly

## Test Procedures
- Only users with `manage_options` can modify role defaults
- Layout and visibility settings persist across sessions
- Reset actions clear user data and return `{ "saved": true }`
- Importing invalid JSON shows an error

## Matrix Checks
- Widgets display correctly per role
- Removed widgets disappear
- New widgets appear after update
- Locked widgets cannot be removed

Status:
- [ ] Verified
- [ ] Needs Testing
