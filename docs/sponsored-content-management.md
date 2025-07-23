---
title: Sponsored Content Management
category: developer
role: developer
last_updated: 2025-07-23
status: complete
---

# Sponsored Content Management Widgets

## SponsoredEventConfigWidget.jsx

This React widget attaches sponsor metadata to an event or post. It saves `sponsor_name`, `sponsor_link` and `sponsor_logo` via the WordPress REST API. Include the component on edit screens when organization admins need to mark content as sponsored.

## SponsorDisplayWidget.php

Adds a disclosure block to the end of single event or post content. If sponsor meta fields are present, the widget displays the logo and a "Sponsored by" note linking to the sponsor URL.
