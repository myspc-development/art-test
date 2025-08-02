<?php
// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WordPress test suite
require_once dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';

// Load your plugin
tests_add_filter('muplugins_loaded', function () {
    require dirname(__DIR__) . '/artpulse.php'; // Adjust if your main plugin file is named differently
});
