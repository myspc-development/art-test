# Dashboard Widget Design Codex

Use the unified styles defined in the [Default Design System Codex](default-design-system-codex.md) when creating or updating dashboard widgets.

- Wrap widget content in `.ap-card` and include a heading element with the class `.ap-card__title`.
- Import the token file at the top of each SCSS module using `@use "../css/tokens.css";`.
- Register widgets through `DashboardWidgetRegistry::register()` so markup and styles load automatically.
- Rebuild assets with `npm run build` whenever you modify files under `src/` or `blocks/` and commit the updated `build/` output.

These steps ensure every widget follows the same layout, theming variables, and accessibility hooks.
