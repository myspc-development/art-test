---
title: Plugin Extension Guide
category: developer
role: developer
last_updated: 2025-08-30
status: complete
---

# ArtPulse Plugin Extension Guide

This document outlines newly suggested features and implementation strategy for extending the ArtPulse Plugin.

## Auto-Tagger Enhancements
The initial `auto-tagger.php` script provides basic metadata detection. We plan to integrate a lightweight ML model to classify images and event descriptions. The AI module triggers on `save_post_artpulse_event` and updates taxonomy terms automatically:

```php
add_action('save_post_artpulse_event', 'ap_ai_describe_event');
function ap_ai_describe_event( $post_id ) {
    // call inference service and assign tags
}
```

See [AI-Enhanced Style/Genre Detection](issues/issue-001-ai-style-genre-detection.md) for research tasks and dataset notes.

## Membership & Ticketing
Paid memberships and ticket sales build on the existing WooCommerce hooks. Orders marked completed should grant a membership level:

```php
add_action('woocommerce_order_status_completed', ['\\ArtPulse\\Core\\WooCommerceIntegration', 'grantMembership']);
```

For implementation details refer to [Monetization & Ticketing Codex](guides/developer/monetization-ticketing-codex.md) and [issue-003-ticketing-membership](issues/issue-003-ticketing-membership.md).

## Monetization Tools
Artists may enable a tip jar and track payouts from the dashboard. Payments run through Stripe or PayPal using the endpoints described in [Artist Monetization Tools Codex](guides/developer/artist-monetization-tools-codex.md). A simple revenue graph summarizes sales per event.

## Developer Utilities
A full test suite is recommended. `npm test` sequentially runs JavaScript, PHP, and end-to-end tests. Individual suites can still be executed with `npm run test:js`, `npm run test:php`, or `npm run test:e2e`. Translation templates are generated with `make-pot` so that strings from `src/` are localizable.

## Deployment Workflow
GitHub Actions provides the default pipeline. Workflow `ci.yml` checks coding standards and builds the plugin:

```yaml
- run: npm run lint
- run: composer sniff
- run: npm run build
```

Release archives are uploaded by `plugin-release.yml`. Adjust secrets for deployment targets as needed.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
