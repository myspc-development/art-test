---
title: Widget Editor Codex Instructions
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Widget Editor Codex Instructions

This guide explains how to document widgets built for the ArtPulse Dashboard. Each widget should have a companion reference that describes its purpose, configuration options and REST endpoints. Follow these standards so that developers and QA engineers can test widgets consistently.

## File Naming
Use lowercase kebab-case for widget documentation. Example: `upcoming-events-widget.md`. Place the file in the `docs/widgets/` folder unless the widget is experimental, in which case use `docs/widgets/drafts/`.

## Required Sections
Every widget guide includes the following sections:

1. **Overview** – A short description and screenshot of the widget in the dashboard.
2. **Props & Settings** – Table listing available attributes, default values and data types. Include code examples for enabling optional features.
3. **Data Flow** – Outline REST endpoints or AJAX actions used to fetch and save data.
4. **Permissions** – Explain which user roles can view or configure the widget and what capabilities are checked.
5. **Testing Steps** – Link to the [Widget QA Checklist](./qa/widget-qa-checklist.md) and describe any widget‑specific scenarios.

## Screenshots
Screenshots help designers understand alignment and color usage. Capture the widget at common breakpoints: 360px (mobile), 768px (tablet) and 1440px (desktop). Place images in a subfolder named after the widget. Reference them with relative paths and short alt text.

## Changelog Notes
Include a short changelog at the bottom of the widget doc if the implementation evolves. Note new props, removed options or important styling changes. Update the `last_updated` date in the frontmatter after each sprint.

## Linking to Code
Where helpful, link directly to the React component or PHP rendering function in the repository. Use GitHub permalinks so lines remain stable even if future commits refactor the code.

Following these instructions keeps widget documentation uniform and easy to navigate. When contributing a new widget, create the doc in a branch, add screenshots and reference the relevant release ticket so reviewers can verify everything works as described.

💬 Found something outdated? Submit Feedback
