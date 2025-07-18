# ArtPulse Plugin

This is a custom WordPress plugin with a full PHPUnit test system and GitHub Actions integration.

---

## 🧰 Local Development Setup

1. Clone the plugin into your `wp-content/plugins` folder:

```bash
git clone https://github.com/YOUR_USERNAME/art-test-main.git
cd art-test-main
composer install
```

2. Copy and configure the test config file:

```bash
cp wp-tests-config.php wp-tests-config.local.php
```

Edit the file to match your local DB and WordPress paths.

## 🧪 Running Tests

1. Create a test DB in MySQL:

```sql
CREATE DATABASE wordpress_test;
```

2. Run tests:

```bash
vendor/bin/phpunit
```

Tests will bootstrap WordPress, load your plugin, and run integration/unit tests.

## 🚀 GitHub Actions

This plugin includes a CI workflow that:

- Installs dependencies
- Sets up a test DB and WP core
- Runs PHPUnit on every push and PR

Check `.github/workflows/phpunit.yml` for details.
