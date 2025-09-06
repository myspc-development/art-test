# Preflight Checklist

- [ ] Build artifacts present (`assets/dist`, `artifacts/build/plugin.zip`)
- [ ] REST contract valid against `schema/dashboard-config.schema.json`
- [ ] Unit/widgets coverage ≥ configured thresholds
- [ ] E2E login (admin + non-admin) ✅
- [ ] Role gating verified (visible/hidden widgets)
- [ ] Axe 0 serious/critical; Lighthouse Perf ≥ 85
- [ ] Packaging + smoke install
- [ ] Rollback path documented
