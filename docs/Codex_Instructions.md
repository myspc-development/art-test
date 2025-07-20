---
title: Codex Instructions
category: developer
role: developer
last_updated: 2025-07-20
status: complete
---

# Codex Instructions

This document defines the conventions used when writing or updating documentation for the ArtPulse plugin. Follow these guidelines whenever Codex GPT generates new files.

## File Organization
- Place documents under the relevant audience folder: `admin/`, `developer/`, `qa/`, `user/`, `widgets/` and so on.
- Use lowercase kebab-case for filenames, e.g. `widget-qa-checklist.md`.
- Keep screenshots in an adjacent `images/` directory when available.

## Frontmatter Format
Every Markdown file begins with YAML frontmatter:

```yaml
title: Your Document Title
category: [widgets|admin|developer|qa]
role: [developer|admin|qa|user]
last_updated: 2025-07-20
status: complete
```

- `category` matches the folder or feature area.
- `role` describes the primary reader.
- `status` is `draft` until the content is finalized.

## Writing Style
- Start with an `H1` heading matching the title.
- Use `H2` and `H3` headings to structure sections logically.
- Include bullet points, tables and code blocks where helpful.
- Refer to other docs using relative links. Example: `[Example Guide](path/to/example.md)`.
- When screenshots are needed, insert placeholders like `![placeholder](images/example.png)`.

## Review Checklist
1. Confirm REST endpoints, hooks and UI components are documented.
2. Provide sample payloads and security notes.
3. Note required user capabilities for each action.
4. Update the changelog if the doc introduces new features.

Following these conventions keeps the Codex consistent and easier to maintain.

## Linking Strategy
Where possible cross-reference sections to avoid duplication. For example the [Widget Registry Reference](widgets/widget-registry-reference.md) explains the registration API, so other guides can simply link to that file. When referencing external resources include a short summary to keep the doc self-contained.

## Keeping Docs Current
After each sprint check the [Verification Checklist](VERIFICATION-CHECKLIST.md) and update the `last_updated` frontmatter date. Remove obsolete TODOs and confirm all relative links resolve. Docs that fall below the 300-word threshold should be expanded with examples or screenshots so they remain comprehensive.