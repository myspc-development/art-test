<?php
$wp_phpunit_dir = getenv('WP_PHPUNIT__DIR');
if (!$wp_phpunit_dir || !is_dir($wp_phpunit_dir)) {
    fwrite(STDERR, "WP_PHPUNIT__DIR is not defined or points to an invalid directory.\n");
    exit(1);
}

if (!defined('WP_TESTS_CONFIG_FILE_PATH') || !file_exists(WP_TESTS_CONFIG_FILE_PATH)) {
    fwrite(STDERR, "WP_TESTS_CONFIG_FILE_PATH is not defined or file does not exist.\n");
    exit(1);
}

ini_set('display_errors', '0');
ini_set('opcache.enable_cli', '0');

require_once $wp_phpunit_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', static function () {
    require_once dirname(__DIR__) . '/artpulse-management.php';
});

require $wp_phpunit_dir . '/includes/bootstrap.php';

