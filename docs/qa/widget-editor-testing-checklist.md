---
title: Widget Editor Testing Checklist
category: qa
role: qa
last_updated: 2025-07-20
status: draft
---

# Widget Editor Testing Checklist

Merged from the Widget Manager testing checklist and verification notes.

## Permissions
- Only users with `manage_options` can modify role defaults.
- Regular users may only edit their own layout via REST calls.

## Persistence
- Layout and visibility settings persist across logins and sessions.

## Final Verification
- Widget registry updated with role metadata
- Dashboard layout endpoints registered
- Admin editor loads roles and widgets via REST