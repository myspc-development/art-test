<?php
// Ensure Composer autoload exists
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Define where the wp-phpunit test suite lives
if (!defined('WP_PHPUNIT__DIR')) {
    define('WP_PHPUNIT__DIR', __DIR__ . '/../vendor/wp-phpunit/wp-phpunit');
}

// Define where WordPress core is installed (Option A: existing site)
if (!defined('WP_PHPUNIT__ABSPATH_DIR')) {
    define('WP_PHPUNIT__ABSPATH_DIR', '/www/wwwroot/192.168.1.21/'); // trailing slash required
}
if (substr(WP_PHPUNIT__ABSPATH_DIR, -1) !== '/') {
    define('WP_PHPUNIT__ABSPATH_DIR', rtrim(WP_PHPUNIT__ABSPATH_DIR, '/') . '/');
}
if (!file_exists(WP_PHPUNIT__ABSPATH_DIR . 'wp-settings.php')) {
    fwrite(STDERR, "ERROR: wp-settings.php not found at " . WP_PHPUNIT__ABSPATH_DIR . "\n");
    exit(1);
}

// Load the wp-phpunit bootstrap
require_once WP_PHPUNIT__DIR . '/includes/functions.php';

// Load the plugin under test when WordPress boots
tests_add_filter('muplugins_loaded', function () {
    $root = dirname(__DIR__);
    // Try common plugin entry files (add more if needed)
    $candidates = [
        $root . '/artpulse.php',
        $root . '/artpulse-management.php',
        $root . '/art-test-main.php',
        $root . '/plugin.php',
    ];
    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Start up the WP testing environment
require_once WP_PHPUNIT__DIR . '/includes/bootstrap.php';
