---
title: Contribution Guide
category: docs
role: developer
last_updated: 2025-07-20
status: draft
---

# Contribution Guide

This guide explains how to propose new documentation or update existing guides in the ArtPulse Codex.

## Proposing Changes
- Open a pull request describing the addition or improvement.
- For minor fixes you can also file an issue using the feedback log.

## Folder and Naming Conventions
- Organize docs under audience folders like `admin/`, `developer/`, `qa/`, `user/` and `widgets/`.
- Use lowercase kebab-case for filenames such as `widget-editor-guide.md`.

## YAML Frontmatter Format
Every Markdown file begins with frontmatter:

```yaml
title: New Guide Name
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
```

Update `last_updated` whenever you modify the document. Change the `status` to `complete` once the guide is finished.

## Style Rules
- Start with an H1 heading matching the title.
- Use `##` for sections and `###` for subsections.
- Wrap code or commands in backticks.
- Reference other docs with relative `.md` links.

## Template
```yaml
title: New Guide Name
category: widgets
role: developer
last_updated: 2025-07-20
status: draft
```

💬 Found something outdated? Submit Feedback
