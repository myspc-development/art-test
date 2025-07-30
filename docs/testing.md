# Running PHPUnit Tests

This plugin uses the official WordPress PHPUnit test suite. To install the suite and WordPress core run:

```bash
bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

After the environment is ready, install Composer dependencies and execute the tests:

```bash
composer install
composer test
```

Set the database credentials in the command if they differ from the defaults. The script downloads WordPress to `/tmp/wordpress` and the test library to `/tmp/wordpress-tests-lib`.
