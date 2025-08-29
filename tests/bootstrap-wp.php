<?php
declare(strict_types=1);
$_tests_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WP PHPUnit not found at {$_tests_dir}\n");
    exit(1);
}
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}
function _manually_load_plugin() {
    $plugin_main = dirname(__DIR__) . '/artpulse.php';
    if (file_exists($plugin_main)) {
        require $plugin_main;
    }
}
require $_tests_dir . '/includes/functions.php';
tests_add_filter('muplugins_loaded', '_manually_load_plugin');
require $_tests_dir . '/includes/bootstrap.php';
