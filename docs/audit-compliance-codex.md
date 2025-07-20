---
title: ArtPulse Codex: Audit & Compliance
category: developer
role: developer
last_updated: 2025-07-20
status: draft
---
# ArtPulse Codex: Audit & Compliance

This guide outlines optional modules for recording activity logs and managing delegated access. The code lives under `src/Core`.

## 1. Activity Logs

`ActivityLogger` stores a record of notable actions in the `ap_activity_logs` table. Each log entry captures the organization ID, user ID, action type, description, IP address, metadata and timestamp.

```php
use ArtPulse\Core\ActivityLogger;
ActivityLogger::log(
    $org_id,
    $user_id,
    'login',
    'User logged in',
    ['extra' => 'data']
);
```

User logins and logouts automatically call the logger.

## 2. Delegated Access

`DelegatedAccessManager` lets organization admins invite temporary collaborators. Records are stored in the `ap_delegated_access` table with an expiry date.

```php
use ArtPulse\Core\DelegatedAccessManager;
$token = DelegatedAccessManager::invite(5, 'user@example.com', ['viewer'], '2025-01-01');
DelegatedAccessManager::accept_invitation($token, $user_id);
```

A daily cron task invokes `DelegatedAccessManager::expire_access()` on the `ap_daily_expiry_check` hook to mark expired access.

## 3. Reviewing Logs

Admins can query or build a custom interface to filter and export entries from these tables. See the tests in `tests/Core` for usage examples.