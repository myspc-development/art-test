<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/TestHelpers.php';

$tests_dir = getenv('WP_TESTS_DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
if (file_exists($tests_dir . '/includes/bootstrap.php')) {
    require_once $tests_dir . '/includes/functions.php';

    tests_add_filter('muplugins_loaded', static function () {
        require dirname(__DIR__) . '/artpulse-management.php';
    });

    require_once $tests_dir . '/includes/bootstrap.php';
}
