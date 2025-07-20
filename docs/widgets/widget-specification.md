---
title: Widget Specification
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
---

# Widget Specification

This document combines the general widget spec with the architecture details.

## Widget Definition
Each widget includes:
- `id` – unique identifier
- `title` – display name
- `component` – React component
- `roles` – allowed roles

## Architecture Overview
The widget system registers components on the client and server. Refer to the
original architecture spec for registration protocols and lifecycle events.
