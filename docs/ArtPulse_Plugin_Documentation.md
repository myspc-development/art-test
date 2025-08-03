---
title: ArtPulse Plugin Documentation
category: docs
role: developer
last_updated: 2025-07-28
status: complete
---

# ArtPulse Plugin Documentation

## \ud83d\udcfd Overview

ArtPulse is a WordPress plugin that empowers artists and organizations with monetization, analytics and AI-driven tools. Key features include:

- **AI-powered tagging** for posts, events and artist profiles using the OpenAI API.
- **Bio summaries** generated automatically by AI to provide short artist introductions.
- Role-based dashboards that tailor widgets and reports based on user capabilities.
- Integration with WooCommerce for selling tickets or merchandise.

## \u2699\ufe0f Installation

1. Upload the plugin files to `wp-content/plugins/artpulse-management` or install via the WordPress admin.
2. Activate **ArtPulse Management** from the Plugins screen.
3. Requirements:
   - WordPress 6.0 or higher
   - PHP 8.0 or higher

## \ud83d\dd27 Admin Settings Guide

The settings page lives under **Settings \u2192 ArtPulse AI**.

1. Enter your OpenAI API key.
2. (Optional) Customize the prompts used for tag generation and bio summaries. Leave the fields blank to use the plugin defaults.
3. Use the built-in tester to send sample text to the REST endpoints and preview the AI response.

Example values:

```text
OpenAI API Key: sk-...
Custom Tag Prompt: Generate relevant tags for this artwork.
Custom Summary Prompt: Summarize this artist bio.
```

## \ud83d\udd10 Permissions and Security

- **REST endpoints** require the `edit_posts` capability.
- **Admin settings** require `manage_options`.
- Nonces are verified on all POST requests, and parameters are sanitized.
- Calls to OpenAI transmit provided text to the external API. Consider your privacy policy when enabling these features.

## \ud83d\udce1 REST API Reference

| Endpoint | Method | Input | Output |
| --- | --- | --- | --- |
| `/wp-json/artpulse/v1/tag` | POST | `{ "text": "art description" }` | `{ "tags": ["abstract", "modern"] }` |
| `/wp-json/artpulse/v1/bio-summary` | POST | `{ "bio": "artist bio..." }` | `{ "summary": "short AI summary..." }` |

## \ud83e\uddd0 Extending ArtPulse

Use hooks to modify AI behavior or add custom logic:

- `artpulse_filter_generated_tags` &ndash; filter the tags array before it is returned.
- `artpulse_ai_prompt_override` &ndash; replace the default prompts sent to OpenAI.

## \ud83d\udc4d Contributing

See [CONTRIBUTING.md](../CONTRIBUTING.md) for guidelines. Follow the [Development Setup guide](development-setup.md) to configure a local environment. Pull requests are welcome on the `main` branch.

## \ud83d\udcdc Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history following [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and semantic versioning.

