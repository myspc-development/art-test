# Preflight Checklist

- [ ] Build: `npm run build`
- [ ] Contract: `curl /wp-json/artpulse/v1/dashboard-config | npx ajv validate -s schema/dashboard-config.schema.json`
- [ ] Unit/widgets: `npm run test:js`
- [ ] E2E: `npm run test:e2e`
- [ ] Accessibility: `npx axe http://localhost/wp-admin?dashboard`
- [ ] Performance: `npm run bundlesize`
- [ ] Packaging: `npm run build && zip -r build/plugin.zip .`
- [ ] Rollback plan documented
