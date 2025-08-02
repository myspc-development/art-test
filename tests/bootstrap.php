<?php
// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WordPress test suite
// Point to the test suite configuration file.
if (!defined('WP_TESTS_CONFIG_FILE_PATH')) {
    define('WP_TESTS_CONFIG_FILE_PATH', dirname(__DIR__) . '/wp-tests-config.php');
}

require_once dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';

// Load your plugin
tests_add_filter('muplugins_loaded', function () {
    require dirname(__DIR__) . '/artpulse.php'; // Adjust if your main plugin file is named differently
});
