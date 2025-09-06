# Audit Report

## Executive Summary
| Subsystem | Status |
|---|---|
| Auth & Roles | 🟡
| Dashboards/Widgets | 🟡
| Content (Events/News/Galleries) | 🔴
| DX/Build | 🔴
| Testing | 🔴
| Performance | 🟡
| Accessibility | 🟡
| Security | 🟡
| Packaging | 🟡
| CI | 🟡

## Findings

### Auth & Roles
- Role presets and widget registry defined but lack tests for role mapping and redirects.
  - Evidence: `RoleDashboard` defines WIDGETS and PRESETS for roles【F:assets/ts/dashboard/RoleDashboard.tsx†L35-L48】
  - Impact: incorrect role mapping could surface wrong widgets.
  - Fix:
    - Add integration test covering login → dashboard redirect for each role.
    - Validate role capabilities when fetching dashboard config.

### Dashboards/Widgets
- REST `/dashboard-config` exposes `excluded_roles` and `capabilities` but schema is minimal and merge logic untested.
  - Evidence: controller builds payload with `capabilities` and `excluded_roles`【F:src/Rest/DashboardConfigController.php†L171-L193】
  - Evidence: schema only requires `widget_roles`【F:schema/dashboard-config.schema.json†L1-L16】
  - Fix:
    - Expand JSON schema to cover `role_widgets`, `layout`, `locked`, `capabilities`, `excluded_roles`.
    - Add unit tests for REST stubs merge when `widget_roles` is empty.

### Content (Events/News/Galleries)
- Cannot reach REST endpoints; curl to events fails (no server)【4264b6†L1-L6】
  - Impact: events/news/galleries features unverified; risk of runtime failures.
  - Fix:
    - Stand up WP instance in CI for content smoke tests.
    - Add graceful empty/error-state tests.

### DX/Build
- `npm run lint:js` reports 289 errors, mostly missing prop-types【cd3492†L1-L20】
- TypeScript typecheck fails due to mismatched component props and potential undefined globals【282ff9†L1-L10】
- Jest coverage thresholds misconfigured; global check reports 0% despite coverage files【97b8bf†L1-L16】
  - Fix:
    - Introduce ESLint rules for props or migrate to TS interfaces.
    - Resolve type errors (e.g., add props for UpcomingEvents component, handle wp.data null).【282ff9†L1-L10】
    - Align `collectCoverageFrom` with actual file locations; exclude `.jsx` if intended.

### Testing
- Coverage summary: Statements 76.08%, Branches 75.86%【fd1a83†L3-L7】; `src/components/ReactForm.jsx` at 0%【4be940†L3-L7】
- Ajv contract validation fails: `Unexpected end of JSON input` (no payload)【317da3†L1-L3】
  - Fix:
    - Add tests for ReactForm and RoleDashboard drag branches.
    - Ensure WP server seeded to serve dashboard-config during contract tests.

### Performance
- No automated Lighthouse budgets; build uses esbuild with default settings.
  - Fix:
    - Script Lighthouse CI targeting dashboard, budget Perf ≥85 and bundle ≤250KB.

### Accessibility
- No automated Axe checks for dashboard/widgets.
  - Fix:
    - Integrate `cypress-axe` in E2E suite; enforce 0 serious/critical violations.

### Security
- Nonce verification exists for `/dashboard-config` save but not for fetch (relies on capability)【F:src/Rest/DashboardConfigController.php†L22-L36】
  - Fix:
    - Ensure consistent nonce checks on state-changing endpoints; audit JWT usage.

### Packaging
- Build script outputs to `assets/dist` but no check for packaged ZIP.
  - Fix:
    - Add packaging step producing plugin ZIP and smoke install test.

### CI
- Workflows cover lint, typecheck, unit, widgets, REST contract, E2E, PHP static analysis.
  - Evidence: `js-lint-typecheck.yml` runs ESLint and tsc【F:.github/workflows/js-lint-typecheck.yml†L1-L28】
- Missing automated Lighthouse, Axe, bundlesize enforcement.
  - Fix:
    - Add `dashboard-lighthouse.yml`, `a11y.yml`, `bundlesize.yml` with caching and step summaries.

## Top 10 Improvements
1. Fix ESLint and TypeScript errors (P1).
2. Expand dashboard-config schema & merge tests (P1).
3. Stand up WP test environment for REST and E2E login (P1).
4. Add unit tests for RoleDashboard drag branches (P1).
5. Implement coverage gating excluding legacy `.jsx` files (P1).
6. Add contract tests and Ajv validation in CI (P1).
7. Integrate Axe and Lighthouse budgets (P2).
8. Package plugin ZIP and smoke install (P2).
9. Add performance budgets and bundlesize check (P2).
10. Harden auth redirects and invalid login UX (P3).

## Assumptions
- Base URL for REST tests: `http://localhost:8889`.
- WordPress server not available; REST calls fail.
- `src/components/ReactForm.jsx` is legacy and may be excluded from coverage.

