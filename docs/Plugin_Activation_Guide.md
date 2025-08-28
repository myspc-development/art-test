---
title: Plugin Activation Guide
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Plugin Activation Guide

This guide explains how to spin up a WordPress instance with Docker and activate the ArtPulse plugin.

## Requirements

- PHP 8.2–8.4
- Node.js 20
- Docker and Docker Compose

## Install PHP Dependencies

```bash
composer install
```

## Build JavaScript Assets

```bash
npm install
npm run build
```

## Setup

1. Copy `docker-compose.yml.example` to `docker-compose.yml`.
2. Run `docker-compose up -d` to start WordPress and the database.
3. Visit `http://localhost:8000` and log in with the credentials defined in the compose file.
4. Place this repository in `wp-content/plugins/artpulse-management` or upload the zipped plugin via the admin.
5. Activate **ArtPulse Management** from the Plugins screen.

The example compose file provides a minimal WordPress stack for development. Adjust ports as needed.

> 💬 *Found something outdated? [Submit Feedback](feedback.md)*
