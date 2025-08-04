<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$wp_bootstrap = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit/includes/bootstrap.php';
$wp_settings = dirname(__DIR__) . '/wordpress/wp-settings.php';

if (file_exists($wp_bootstrap) && file_exists($wp_settings)) {
    if (!defined('WP_TESTS_CONFIG_FILE_PATH')) {
        define('WP_TESTS_CONFIG_FILE_PATH', dirname(__DIR__) . '/wp-tests-config.php');
    }
    require_once $wp_bootstrap;
    tests_add_filter('muplugins_loaded', function () {
        require dirname(__DIR__) . '/artpulse.php';
    });
} elseif (!function_exists('tests_add_filter')) {
    function tests_add_filter(...$args): void {}
}
