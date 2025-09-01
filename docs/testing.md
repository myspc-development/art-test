# Running PHPUnit Tests

Follow the [development setup guide](development-setup.md) to install dependencies and prepare WordPress for testing. Once configured, run:

```bash
composer test
```

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
