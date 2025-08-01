<?php
// Load Composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';

// Include WordPress PHPUnit polyfills
if (!defined('WP_TESTS_PHPUNIT_POLYFILLS_PATH')) {
    define(
        'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
        dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php'
    );
}

// Include function stubs used by tests
require __DIR__ . '/TestHelpers/FunctionStubs.php';

