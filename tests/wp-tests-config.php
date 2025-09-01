<?php
// DB creds for the tests database (it will be dropped/created by the suite)
define('DB_NAME',     'wordpress_test');
define('DB_USER',     'wordpress_test');
define('DB_PASSWORD', '0preggers2');
define('DB_HOST',     '127.0.0.1');

// Required by wp-phpunit:
define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL',  'admin@example.org');
define('WP_TESTS_TITLE',  'WP Tests');

// Use the PHP running this process:
if (!defined('WP_PHP_BINARY')) {
    define('WP_PHP_BINARY', PHP_BINARY);
}

// Speed up tests:
define('WP_DEBUG', true);
define('DISABLE_WP_CRON', true);
define('WP_MEMORY_LIMIT', '256M');

// Table prefix for tests (keep distinct from local dev site):
$table_prefix = 'wptests_';

// If you need multisite later:
// define('WP_TESTS_MULTISITE', true);
