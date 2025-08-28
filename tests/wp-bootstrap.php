<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('opcache.enable_cli', '0');

$lib = getenv('WP_PHPUNIT__DIR') ?: 'vendor/wp-phpunit/wp-phpunit';
if (!is_dir($lib)) {
    fwrite(STDERR, "WP_PHPUNIT__DIR not found at $lib\n");
    exit(1);
}
if (!defined('WP_TESTS_CONFIG_FILE_PATH')) {
    define('WP_TESTS_CONFIG_FILE_PATH', dirname(__DIR__) . '/wp-tests-config.php');
}
if (!defined('WP_PHP_BINARY')) {
    define('WP_PHP_BINARY', '/usr/bin/php');
}

require $lib . '/includes/bootstrap.php';

tests_add_filter('muplugins_loaded', static function () {
    require dirname(__DIR__) . '/artpulse-management.php';
});
