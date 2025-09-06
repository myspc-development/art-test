# Dashboard Audit Report

| Area | Severity | File(s) | Why it matters | Fix |
| --- | --- | --- | --- | --- |
| Dependencies | P1 | package.json | Misaligned TypeScript and testing libraries can break builds and slow installs | Align TypeScript 5.6+ with ts-jest, ajv, testing libs; drop unused packages |
| Scripts | P2 | package.json | Build/test commands inconsistent across environments | Normalize build, dev, test:js, typecheck, lint:js; output to assets/dist |
| Role gating logic | P1 | assets/ts/dashboard/** | Implementation lacks precedence tests for excludeRoles > capability > roles | Add unit tests covering each branch |
| REST config merge | P1 | server endpoints; assets/ts/dashboard/widgets | Client and server widget lists may clobber props | Merge by id preserving rest-only properties; add tests for [] and specific role arrays |
| UI states & A11y | P2 | assets/ts/dashboard/index.tsx, widgets | Loading/error/empty states miss accessible copy and aria-live regions | Ensure visible text and labels; add tests |
| Authentication flow | P2 | login, redirects | Missing tests for invalid login and role-based landing pages | Add e2e specs to verify redirects and messages |
| CI workflows | P3 | .github/workflows/* | Workflows missing caching and markdown summaries | Add caching, timeouts, $GITHUB_STEP_SUMMARY, and artifact uploads |
| Packaging | P3 | build scripts | No automated plugin zip verification | Extend CI to build ZIP and smoke test in clean WP |
