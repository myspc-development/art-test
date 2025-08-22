<?php
// 1) Composer autoload so any dev libs + your PSR-4 classes resolve.
require dirname(__DIR__) . '/vendor/autoload.php';

// 2) Ensure the WP test config is set (fall back to local)
if (!getenv('WP_PHPUNIT__TESTS_CONFIG')) {
    putenv('WP_PHPUNIT__TESTS_CONFIG=' . dirname(__DIR__) . '/tests/wp-tests-config.local.php');
}

// 3) Load shared test helpers (centralized stubs etc.)
$helpers = [
    __DIR__ . '/TestHelpers/FrontendFunctionStubs.php',
    // add more helper files here if you have Core/Admin helpers
];
foreach ($helpers as $file) {
    if (is_file($file)) require_once $file;
}

// 4) Hand off to the WordPress PHPUnit bootstrap
require dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';
