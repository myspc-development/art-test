<?php
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('WP_TESTS_DB_NAME') ?: getenv('DB_NAME') ?: 'wordpress_test');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('WP_TESTS_DB_USER') ?: getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', getenv('WP_TESTS_DB_PASSWORD') ?: getenv('DB_PASSWORD') ?: '');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('WP_TESTS_DB_HOST') ?: getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', getenv('WP_TESTS_DB_CHARSET') ?: 'utf8mb4');
}
if (!defined('DB_COLLATE')) {
    define('DB_COLLATE', getenv('WP_TESTS_DB_COLLATE') ?: '');
}
$table_prefix = getenv('WP_TESTS_TABLE_PREFIX') ?: 'wptests_';

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WP_PHP_BINARY')) {
    define('WP_PHP_BINARY', getenv('WP_PHP_BINARY') ?: (defined('PHP_BINARY') ? PHP_BINARY : 'php'));
}

if (!defined('WP_TESTS_DOMAIN')) {
    define('WP_TESTS_DOMAIN', getenv('WP_TESTS_DOMAIN') ?: 'example.org');
}
if (!defined('WP_TESTS_EMAIL')) {
    define('WP_TESTS_EMAIL', getenv('WP_TESTS_EMAIL') ?: 'admin@example.org');
}
if (!defined('WP_TESTS_TITLE')) {
    define('WP_TESTS_TITLE', getenv('WP_TESTS_TITLE') ?: 'WordPress Test Site');
}
if (!defined('WPLANG')) {
    define('WPLANG', '');
}
if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

