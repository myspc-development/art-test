<?php
// WordPress integration bootstrap.
declare(strict_types=1);

$_tests_dir = getenv('WP_PHPUNIT__DIR') ?: dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';

if (!is_file($_tests_dir . '/wordpress/wp-settings.php')) {
    fwrite(
        STDERR,
        "Could not find WordPress tests in {$_tests_dir}. " .
        "Run tools/provision-wp-core.sh to install the test library.\n"
    );
    exit(1);
}

// Be strict: hide PHP notices and disable opcode cache.
ini_set('display_errors', '0');
ini_set('opcache.enable_cli', '0');

require $_tests_dir . '/includes/functions.php';

// Load the plugin under test.
tests_add_filter('muplugins_loaded', function () {
    $main = getenv('PLUGIN_MAIN_FILE') ?: 'artpulse-management.php';
    require dirname(__DIR__) . '/' . $main;
});

// Start WordPress.
require $_tests_dir . '/includes/bootstrap.php';
