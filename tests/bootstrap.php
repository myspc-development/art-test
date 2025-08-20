<?php
require_once __DIR__ . '/../vendor/autoload.php';
$_tests_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
require_once $_tests_dir . '/includes/functions.php';
function _manually_load_plugin() {
    require dirname(__DIR__) . '/artpulse-management.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');
$_config_file = __DIR__ . '/wp-tests-config.php';
if (file_exists($_config_file)) {
    putenv('WP_PHPUNIT__TESTS_CONFIG=' . $_config_file);
}
require $_tests_dir . '/includes/bootstrap.php';

