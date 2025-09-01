<?php
// tests/bootstrap-wp.php
declare(strict_types=1);

/**
 * Deterministic WP test bootstrap for the WP suite.
 * - Requires wp-phpunit bootstrap (defines ABSPATH)
 * - Fails early with a helpful message if core isn't linked
 * - Enables AP test mode conveniences only AFTER WP is loaded
 */

$root = dirname(__DIR__);

// Ensure the plugin’s expected wp-phpunit dir and config are set.
if (!getenv('WP_PHPUNIT__DIR')) {
    putenv('WP_PHPUNIT__DIR=' . $root . '/vendor/wp-phpunit/wp-phpunit');
}
if (!getenv('WP_PHPUNIT__TESTS_CONFIG')) {
    putenv('WP_PHPUNIT__TESTS_CONFIG=' . $root . '/tests/wp-tests-config.php');
}

$wpPhpUnit = getenv('WP_PHPUNIT__DIR');
$config    = getenv('WP_PHPUNIT__TESTS_CONFIG');

// Hard fail if the WordPress core symlink isn’t present.
$coreWpSettings = $wpPhpUnit . '/wordpress/wp-settings.php';
if (!is_file($coreWpSettings)) {
    fwrite(STDERR, "WordPress core not found at {$wpPhpUnit}/wordpress\n".
        "Run: WP_CORE_DIR=/absolute/path/to/wordpress-develop/build php bin/wp-core-link\n");
    exit(1);
}

// ✅ Load the official WP test bootstrap (this defines ABSPATH and loads WP)
require $wpPhpUnit . '/includes/bootstrap.php';

// From here on, WordPress is loaded and ABSPATH is defined.
if (!defined('AP_TEST_MODE')) {
    define('AP_TEST_MODE', 1);
}

// Preload REST routes (mirrors how your tests expect routes to exist).
// If your plugin registers routes on `rest_api_init`, do this once:
if (function_exists('do_action')) {
    do_action('rest_api_init');
}

// Optional: if your REST auth utils expect a logged-out baseline by default,
// explicitly clear current user to ensure unauth checks behave as expected.
if (function_exists('wp_set_current_user')) {
    wp_set_current_user(0);
}
