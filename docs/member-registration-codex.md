---
title: ArtPulse Codex: Member Registration
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---
# ArtPulse Codex: Member Registration

This guide explains how to enable WordPress registration for members and automatically route them to the member dashboard.

## 1. Enable Native Registration

1. In the WordPress admin go to **Settings → General**.
2. Check **Anyone can register**.
3. Set the **New User Default Role** to **Subscriber**. The plugin will convert this role to **member** when users sign up.

## 2. Assign the Member Role

Add a `user_register` action to update the role if needed:

```php
add_action('user_register', function ($user_id) {
    $user = new WP_User($user_id);
    if (in_array('subscriber', (array) $user->roles, true)) {
        $user->set_role('member');
    }
});
```

## 3. Redirect After Registration

Send new members straight to their dashboard:

```php
add_filter('wp_registration_redirect', function () {
    return home_url('/dashboard/member');
});
```

## 4. Register as Member Button

Place this link anywhere to send visitors to the default registration form:

```php
<a href="<?php echo esc_url(wp_registration_url()); ?>" class="button button-primary">
    Register as a Member
</a>
```

## 5. Optional Auto-login

WordPress does not log users in after registration by default. If you want to log them in immediately, use a custom form like the `[ap_register]` shortcode and call `wp_set_current_user()` and `wp_set_auth_cookie()` after `wp_create_user()` succeeds.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
