<?php
declare(strict_types=1);

// Load Composer autoload to register plugin classes.
$pluginRoot = dirname(__DIR__);
$autoload = $pluginRoot . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Load the test configuration which defines DB creds and ABSPATH.
$config = $pluginRoot . '/tests/wp-tests-config.php';
if (!file_exists($config)) {
    fwrite(STDERR, "Missing tests/wp-tests-config.php. Copy tests/wp-tests-config-sample.php to tests/wp-tests-config.php and fill in the database details.\n");
    exit(1);
}
require_once $config;

// Locate the WordPress PHPUnit bootstrap directory.
$phpunitDir = getenv('WP_PHPUNIT__DIR');
if (!$phpunitDir) {
    $vendorDir = $pluginRoot . '/vendor/wp-phpunit/wp-phpunit';
    if (is_dir($vendorDir)) {
        $phpunitDir = $vendorDir;
    } else {
        $localDir = dirname($pluginRoot) . '/wordpress-develop/tests/phpunit';
        if (is_dir($localDir)) {
            $phpunitDir = $localDir;
        } else {
            fwrite(STDERR, "Could not locate the WordPress test suite. Set WP_PHPUNIT__DIR or install wp-phpunit.\n");
            exit(1);
        }
    }
}

// Load WordPress test functions so we can hook into plugin loading.
require_once $phpunitDir . '/includes/functions.php';

// Tell WordPress to load this plugin once the testing environment is set up.
tests_add_filter('muplugins_loaded', static function () use ($pluginRoot): void {
    // Load the plugin's main file.
    require $pluginRoot . '/artpulse-management.php';
});

// Include test stubs after hooking the plugin.
require_once $pluginRoot . '/tests/Frontend/_stubs.php';

// Finally, bootstrap WordPress so the tests can run.
require_once $phpunitDir . '/includes/bootstrap.php';
