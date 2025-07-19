# Contributing

Thank you for your interest in contributing to ArtPulse. Development requires **PHP 8.2 or higher** and WordPress 6.8. To get started clone the repository and install the PHP and Node dependencies:

```bash
composer install       # install PHP libraries
npm install            # install Node packages
npx husky install      # activate Git hooks
npm run build          # compile JavaScript assets
bash scripts/setup-env.sh
```
The script uses `svn` to download WordPress. Install Subversion if the command is missing.
The `setup-env.sh` script will also install Composer dependencies when the
`CI` environment variable is present, allowing automated test pipelines to run
without a separate install step.

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
vendor/bin/phpunit
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

## Branching Strategy

Create short-lived feature branches from `main` using the pattern `feature/<description>` or `bugfix/<description>`. Submit pull requests early for review and keep commits focused on a single change.

## Coding Standards

The project follows the [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) and ESLint recommended rules. Run `composer sniff` to check PHP files and `npm run lint` to verify JavaScript code.
