# Contributing

Thank you for your interest in contributing to ArtPulse. Development requires **PHP 8.2 or higher** and WordPress 6.8. The following commands install dependencies and bootstrap the local environment.

```bash
composer install
npm install
bash scripts/setup-env.sh
```

`setup-tests.sh` relies on database credentials supplied via environment
variables. Export these variables or create a `.env` file before running the
script:

```bash
DB_NAME=your_test_db
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_HOST=localhost
```

Run the automated test suite and coding standards checks with:

```bash
composer test
composer sniff
```

These tools are described in the [Development Setup](README.md#development-setup) section of the README. For more details on the testing workflow see [docs/testing-strategy.md](docs/testing-strategy.md).

When working on JavaScript sources or block files make sure to rebuild the
compiled assets so `wp_enqueue_script()` loads valid code. React components are
bundled with Rollup. Run the following command and commit the updated files under
`assets/js`:

```bash
npm run build
```
