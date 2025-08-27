<?php
// WordPress integration bootstrap.
declare(strict_types=1);

$_tests_dir = getenv('WP_PHPUNIT__DIR');
if (!$_tests_dir) {
    fwrite(STDERR, "WP_PHPUNIT__DIR is not set. Run tools/provision-wp-core.sh first.\n");
    exit(1);
}

require $_tests_dir . '/includes/functions.php';

// Load the plugin under test.
tests_add_filter('muplugins_loaded', function () {
    $main = getenv('PLUGIN_MAIN_FILE') ?: 'artpulse-management.php';
    require dirname(__DIR__) . '/' . $main;
});

// Start WordPress.
require $_tests_dir . '/includes/bootstrap.php';
