<?php
define('DB_NAME',     getenv('DB_NAME')     ?: 'wordpress_test');
define('DB_USER',     getenv('DB_USER')     ?: 'wordpress_test');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '0preggers2');
define('DB_HOST',     getenv('DB_HOST')     ?: '127.0.0.1');

define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
$table_prefix = 'wptests_';

define('WP_DEBUG', true);
define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL',  'admin@example.org');
define('WP_TESTS_TITLE',  'WP Tests');
define('WP_PHP_BINARY', PHP_BINARY);

// Point ABSPATH at the clean core we just copied
define('ABSPATH', dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit/wordpress/');
