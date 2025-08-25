---
title: Roles Dashboard Manual QA
category: qa
role: tester
last_updated: 2025-08-16
status: draft
---

# Roles Dashboard Manual QA

## Checklist

- [ ] Toggle `roles_dashboard_v2` feature flag.
- [ ] Log in as a Member and confirm the dashboard loads without errors.
- [ ] Log in as an Artist and confirm role widgets render.
- [ ] Log in as an Organization Admin and confirm role assignments.
- [ ] Verify no JavaScript errors appear in the console.
- [ ] Confirm unauthorized users are redirected.
- [ ] Validate responsive layout on mobile and desktop.

## Browser Matrix

| Browser | Windows 11 | macOS Ventura | iOS 17 | Android 14 |
| --- | --- | --- | --- | --- |
| Chrome (latest) | ✅ | ✅ | n/a | ✅ |
| Firefox (latest) | ✅ | ✅ | n/a | n/a |
| Safari (latest) | n/a | ✅ | ✅ | n/a |
| Edge (latest) | ✅ | n/a | n/a | n/a |
