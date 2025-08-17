<?php
/**
 * PHPUnit bootstrap for WordPress integration tests.
 */

$wp_phpunit_dir = getenv('WP_PHPUNIT__DIR');
if (!$wp_phpunit_dir) {
    $wp_phpunit_dir = __DIR__ . '/../../vendor/wp-phpunit/wp-phpunit';
}

// Load test functions so we can load the plugin.
require_once $wp_phpunit_dir . '/includes/functions.php';

tests_add_filter('muplugins_loaded', function () {
    $plugin_main = getenv('PLUGIN_MAIN_FILE') ?: 'artpulse.php';

    $root = realpath(__DIR__ . '/../../');
    $candidate = $root . '/' . ltrim($plugin_main, '/');

    if (! file_exists($candidate)) {
        $alt = $root . '/src/Core/Plugin.php';
        if (file_exists($alt)) {
            require_once $alt;
            return;
        }
        fwrite(STDERR, "\n[bootstrap] Could not find plugin main file at: {$candidate}\nSet PLUGIN_MAIN_FILE env var or update phpunit.xml.dist.\n");
    } else {
        require_once $candidate;
    }
});

// Ensure tests config path is available to the wp-phpunit bootstrap.
$tests_config = getenv('WP_PHPUNIT__TESTS_CONFIG') ?: __DIR__ . '/wp-tests-config.php';
if (! file_exists($tests_config)) {
    fwrite(STDERR, "\n[bootstrap] Missing tests config at {$tests_config}. Create tests/php/wp-tests-config.php or set WP_PHPUNIT__TESTS_CONFIG.\n");
}

// Start up the WP testing environment (this loads WP core from wp-phpunit package).
require $wp_phpunit_dir . '/includes/bootstrap.php';
