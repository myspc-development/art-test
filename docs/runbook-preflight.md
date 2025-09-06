# Preflight Runbook

## Lint & Typecheck
```bash
npm run lint:js
npm run typecheck
```

## Unit & Widget Tests
```bash
npx jest --runInBand --coverage --testPathPattern '(widgets|Dashboard)' --coverageReporters=text-summary
# summary: coverage/summary.txt
```

## REST Contract
```bash
BASE=http://localhost:8889
curl -s $BASE/wp-json/artpulse/v1/dashboard-config | jq . > artifacts/contract/dashboard-config.json
npx -y ajv-cli validate -s schema/dashboard-config.schema.json -d artifacts/contract/dashboard-config.json
```

## E2E Smoke
```bash
# Seed users & config
wp user create admin admin@example.com --role=administrator --user_pass=password
wp user create member member@example.com --role=author --user_pass=password
wp option update artpulse_dashboard_config < tests/fixtures/dashboard-config.json
# Run
npx playwright test tests/e2e/login.spec.ts --trace on-first-retry
# traces: artifacts/e2e/login/trace.zip
```

## Accessibility
```bash
# from Cypress tests
npx cypress run --spec cypress/e2e/axe.cy.ts
# report: artifacts/a11y/axe.json
```

## Performance
```bash
npx lighthouse http://localhost:8889/wp-admin/admin.php?page=ap-dashboard \
  --output=json --output-path=artifacts/lighthouse/dashboard.json \
  --budget-path=tests/perf/budget.json
```

## Packaging
```bash
npm run build
zip -r artifacts/build/plugin.zip . -x node_modules/\* tests/\* .git/\*
wp plugin install artifacts/build/plugin.zip --force
```

## Rollback
```bash
wp plugin deactivate artpulse-management
wp plugin delete artpulse-management
```
