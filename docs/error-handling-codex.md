# ArtPulse Codex: Error Handling

This reference covers best practices for dealing with failures in both PHP and JavaScript.

## 1. Backend

- Use `WP_Error` objects when a REST endpoint or AJAX action encounters a problem.
- Log unexpected exceptions with `error_log()` or your preferred monitoring tool.
- Return clear HTTP status codes (e.g. 401, 403, 404) so clients can react appropriately.

## 2. Frontend

- Check `response.ok` after every `fetch` call and show a friendly message when an error occurs.
- Wrap expensive operations in `try/catch` blocks and report errors to the console for debugging.
- Provide fallback UI when the user lacks permission or connectivity is lost.
