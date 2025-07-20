---
title: Widget Layout Import  Export Guide
category: widgets
role: admin
last_updated: 2025-07-20
status: complete
---

# Widget Layout Import & Export Guide

Admins can copy layouts between sites or create backups using the Import/Export tools in the dashboard editor.

## Exporting
1. Choose a role in the editor.
2. Click **Copy Export**. The current layout is copied to your clipboard as JSON.
3. Save this JSON in a file or paste it into another site.

Example export:
```json
[
  { "id": "membership", "visible": true, "position": 1 }
]
```

## Importing
1. Paste a layout JSON block into the **Import Layout** field.
2. Click **Import**. The layout updates immediately if the JSON is valid.
3. Unknown widget IDs are ignored so layouts from other sites remain compatible.

Always validate the JSON structure before saving to avoid corrupt layouts.

> 💬 *Found something outdated? [Submit Feedback](../feedback.md)*
