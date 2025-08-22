<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (!getenv('WP_PHPUNIT__TESTS_CONFIG')) {
    putenv('WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.local.php');
}

require_once __DIR__ . '/TestHelpers/FrontendFunctionStubs.php';
require_once __DIR__ . '/TestHelpers.php';

require_once __DIR__ . '/../vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';

