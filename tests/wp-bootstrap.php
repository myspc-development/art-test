<?php
// WordPress integration bootstrap.
declare(strict_types=1);

$_tests_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';

require $_tests_dir . '/includes/functions.php';

// Load the plugin under test.
tests_add_filter('muplugins_loaded', function () {
    $main = getenv('PLUGIN_MAIN_FILE') ?: 'artpulse-management.php';
    require dirname(__DIR__) . '/' . $main;
});

// Start WordPress.
require $_tests_dir . '/includes/bootstrap.php';
