---
title: For You (Member)
category: widgets
role: developer
last_updated: 2025-07-24
status: complete
---

# For You (Member)

**Widget ID:** `widget_for_you_member`

**Roles:** member, artist

## Description
Personalized events and posts. Preferences can be adjusted via the profile settings panel.

Content is calculated server side using the current user profile and follow list. Results come from `GET /wp-json/artpulse/v1/recommendations`.

### Customization
- The member can refine recommendations through the **For You Settings** modal in their profile.
- Admins may override the API endpoint using the `ap_for_you_endpoint` filter to test alternative services.

