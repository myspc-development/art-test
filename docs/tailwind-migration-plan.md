# Tailwind Migration Plan

The project currently relies on SCSS files in `assets/scss/` which compile into
`assets/css/ap-style.css`. We are migrating these styles to Tailwind utility
classes. Standard layout and component conventions are summarized in the
[Tailwind Design Guide](tailwind-design-guide.md).

## Goals
- Replace each SCSS module with Tailwind classes directly in templates and
  components.
- Remove `ap-style.css` and the SCSS source files once all pages are converted.
- Simplify the build process so Tailwind is the only CSS pipeline.

## Migration Steps
1. Audit `assets/scss/` and list the templates that depend on each file.
2. For each template, rebuild the layout using Tailwind utilities.
3. Delete the associated SCSS file and compiled CSS after verifying visual
   parity.
4. Update `package.json` to drop the `build:css` script when no SCSS remains.
5. Remove `ap-style.css` from enqueue hooks and repository.

Progress can be tracked in this document as sections are completed.
