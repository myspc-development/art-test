<?php
// Use environment overrides if present, else fall back to these defaults.
define('DB_NAME', getenv('WP_TESTS_DB_NAME') ?: 'wordpress_test');
define('DB_USER', getenv('WP_TESTS_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('WP_TESTS_DB_PASSWORD') ?: 'CHANGE_ME');
define('DB_HOST', getenv('WP_TESTS_DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
$table_prefix = 'wptests_';

// Optional toggles
// define('WP_DEBUG', true);
// define('WP_ALLOW_MULTISITE', false);
