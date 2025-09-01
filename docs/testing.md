# Running PHPUnit Tests

Follow the [development setup guide](development-setup.md) to install dependencies and prepare WordPress for testing. Once configured, run the full test suite:

```bash
npm test
```

To run only the PHP tests:

```bash
npm run test:php
```

## Test Buckets

Every PHP test file includes a `@group` annotation naming its bucket. Valid
bucket names are:

- `AI`
- `AJAX`
- `ADMIN`
- `BLOCKS`
- `CLI`
- `COMMUNITY`
- `CORE`
- `CRM`
- `DB`
- `ENGAGEMENT`
- `FRONTEND`
- `INTEGRATION`
- `MONETIZATION`
- `PAYMENT`
- `PERSONALIZATION`
- `REPORTING`
- `REST`
- `SAFETY`
- `SEARCH`
- `SMOKE`
- `SUPPORT`
- `UNIT`
- `WIDGETS`
- `WPUNIT`
- `PHPUNIT`
- `ENVIRONMENT`

Run the bucket validator with:

```bash
npm run lint:test-groups
```

The script fails if a test is missing a bucket or uses a name outside the
list above.

## End-to-End Tests

Playwright tests exercise dashboard interactions inside a WordPress
instance running in Docker. The containers are started once for the
entire test run and each test resets user dashboard state so the
environment can be reused without a full teardown.

```bash
docker compose -f docker-compose.yml.example up -d --wait  # start
npm run test:e2e                                          # run Playwright
docker compose -f docker-compose.yml.example down -v      # stop
```
