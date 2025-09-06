# Preflight Runbook

```bash
npm ci
npm run build
npm run test:js
curl -s http://localhost/wp-json/artpulse/v1/dashboard-config > build/contract.json
npx ajv validate -s schema/dashboard-config.schema.json -d build/contract.json
npx playwright test tests/e2e/login-dashboard.spec.ts
npx axe http://localhost/wp-admin?page=dashboard --save build/axe.json
npm run bundlesize > build/bundlesize.txt
zip -r build/artpulse.zip .
```

Artifacts:
- `build/contract.json`
- `build/coverage-*`
- `build/junit-*.xml`
- `build/e2e/*`
- `build/axe.json`
- `build/bundlesize.txt`
- `build/artpulse.zip`
