# Contributing

Thank you for your interest in contributing to ArtPulse. Development requires **PHP 8.2 or higher** and WordPress 6.8. The following commands install dependencies and bootstrap the local environment.

```bash
composer install
npm install
bash scripts/setup-env.sh
```

Run the automated test suite and coding standards checks with:

```bash
composer test
composer sniff
```

These tools are described in the [Development Setup](README.md#development-setup) section of the README. For more details on the testing workflow see [docs/testing-strategy.md](docs/testing-strategy.md).
