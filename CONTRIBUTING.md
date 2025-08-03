# Contributing

Thank you for your interest in contributing to ArtPulse. Development requires **PHP 8.2 or higher** and WordPress 6.8. Follow the [Development Setup guide](docs/development-setup.md) to install dependencies, build assets, and prepare the test environment.

Run the automated test suite and coding standards checks with:

```bash
vendor/bin/phpunit
composer sniff
```

Run the widget manifest consistency check to ensure every widget file is
listed in both manifest files and that each manifest entry references an
existing file:

```bash
bash tests/check-widget-manifests.sh
```

These tools are documented in [docs/development-setup.md](docs/development-setup.md). For more details on the testing workflow see [docs/testing-strategy.md](docs/testing-strategy.md).

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

## Documentation Guidelines

Documentation lives in the `docs/` directory and is organized by audience folders (admin, developer, qa, user). Each folder has a `README.md` that lists key articles in order.

When adding a new document:
1. Include frontmatter with `title`, `category`, `role`, `last_updated`, and `status`.
2. Use relative links to other docs.
3. Update `docs/index.md` so readers can discover the new guide.
4. Keep filenames lowercase with hyphens.

Questions or suggestions can be filed via the link in [docs/feedback.md](docs/feedback.md).


