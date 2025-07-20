---
title: Widget QA Checklist
category: widgets
role: developer
last_updated: 2025-07-20
status: complete
---

Combine these verification steps when testing the widget manager and individual widgets.
This list covers functional behavior as well as visual checks. Run through each section whenever a new widget is created or when the manager receives updates. Items are grouped by category so QA engineers can delegate tasks among the team. Mark each item as verified in your test report to ensure consistent coverage across browser environments and user roles.

## Render Check
- [ ] Widget title displays correctly
- [ ] Placeholder content replaced with real data
- [ ] No PHP notices or JavaScript errors

## Data Save
- [ ] Widget options save via AJAX
- [ ] REST API returns updated values
- [ ] Invalid data shows an inline error

## Responsive Layout
- [ ] Widgets stack properly on mobile
- [ ] Grid adjusts on tablet and desktop
- [ ] Collapse/expand controls work at all sizes

## Permissions
- [ ] Only authorized roles can view the widget
- [ ] Editing restricted to users with `manage_options`
- [ ] Hidden widgets remain inaccessible via direct REST calls

## Browser Compatibility
- [ ] Render tested in latest Chrome, Firefox and Safari
- [ ] Mobile Safari and Chrome show no layout shifts
- [ ] Keyboard navigation works consistently across browsers

## Accessibility
- [ ] ARIA labels present for interactive controls
- [ ] Sufficient color contrast for text and icons
- [ ] Widget is usable without a mouse

## Performance
- [ ] Widget loads within two seconds on a fresh page view
- [ ] No excessive network requests
- [ ] Memory usage stays under 50 MB after multiple edits

Status:
- [ ] Verified
- [ ] Needs Testing
