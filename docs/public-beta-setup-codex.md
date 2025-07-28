---
title: ArtPulse Codex: Public Beta Setup
category: developer
role: developer
last_updated: 2025-07-28
status: draft
---
# ArtPulse Codex: Public Beta Setup

This codex describes how to enable a limited public beta program with invite-only access, onboarding guidance and feature flags.

> **Note**
> The `ap_beta_invites` table and related REST endpoint are not yet implemented. This document outlines proposed future functionality and may change when development begins.

## 1. Beta Access & Invite System

Create the `ap_beta_invites` table and expose an endpoint to redeem codes.

```sql
CREATE TABLE `ap_beta_invites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(16) UNIQUE,
  `type` ENUM('artist','gallery','admin'),
  `redeemed_by` BIGINT NULL,
  `created_at` DATETIME,
  `redeemed_at` DATETIME
);
```

Redeem codes via:

```http
POST /artpulse/v1/beta/redeem
```

When valid, assign the role, mark the invite as used and store `_joined_beta` in `user_meta`.

## 2. Onboarding Wizard

After redeeming a beta code, an `OnboardingWizard.jsx` component runs post login.
It collects role selection, avatar/org name and an initial event or venue follow.
Progress is stored in `user_meta` so the wizard only shows once.

## 3. Feature Flags

A `config/feature-flags.json` file holds boolean flags. Use the `ap_feature_enabled()` helper to load them.

```php
function ap_feature_enabled($key) {
    static $flags = null;
    if (!$flags) {
        $flags = json_decode(file_get_contents(__DIR__ . '/../config/feature-flags.json'), true);
    }
    return $flags[$key] ?? false;
}
```

Wrap unfinished features with this helper so they can be toggled per deploy.

## 4. Beta Badges & Hints

Display a dashboard notice and a footer version string to remind users they are in beta.

```php
echo '<div class="notice notice-info">ArtPulse Beta – features may change.</div>';
```

Footer example:

```html
<p style="opacity: 0.5">Running <strong>BETA</strong> version <?= ART_PULSE_VERSION ?></p>
```

## 5. Feedback & Opt-Out

Provide a "Give Feedback" button linking to a form or issue tracker. Users can leave the beta by deleting their `_joined_beta` meta entry.

```php
delete_user_meta($user_id, '_joined_beta');
```

## QA Checklist

| Action | Expected Result |
| --- | --- |
| Redeem invite code | Role is assigned, beta flag set |
| Complete onboarding | `_onboarded` meta saved |
| Incomplete features | Hidden behind flag |
| Feature flags reload on deploy | Config is cached per request |
| Feedback form works | Submits without error |

## Developer Checklist

- Invite table and redeem endpoint
- Onboarding wizard renders on first login
- `feature-flags.json` created and helper active
- UI shows beta badge and links
- Feedback widget added

## Related Docs

Update these codex modules when implementing:

- Access Control → Beta Invite Flow
- Frontend Components → OnboardingWizard.jsx
- Deployment & Config → Feature Flags
- User Roles → Expand beta_user, onboarded

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
