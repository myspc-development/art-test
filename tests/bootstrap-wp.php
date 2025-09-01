<?php
declare(strict_types=1);

/**
 * Deterministic WordPress test bootstrap.
 * - Verifies the wp-phpunit path & tests config
 * - Fails early if the WordPress core symlink is missing
 * - Loads the official wp-phpunit bootstrap (defines ABSPATH)
 * - Preloads REST routes; clears current user for unauth checks
 */

$root = dirname(__DIR__);

if (!getenv('WP_PHPUNIT__DIR')) {
    putenv('WP_PHPUNIT__DIR=' . $root . '/vendor/wp-phpunit/wp-phpunit');
}
if (!getenv('WP_PHPUNIT__TESTS_CONFIG')) {
    putenv('WP_PHPUNIT__TESTS_CONFIG=' . $root . '/tests/wp-tests-config.php');
}

$wpPhpUnit = getenv('WP_PHPUNIT__DIR');
$config    = getenv('WP_PHPUNIT__TESTS_CONFIG');

if (!is_file($config)) {
    $sample = dirname($config) . '/wp-tests-config-sample.php';
    if (is_file($sample)) {
        copy($sample, $config);
        fwrite(STDERR, "Created default tests config at: {$config}\n");
    } else {
        fwrite(STDERR, "Missing tests config at: {$config}\n" .
            "Copy tests/wp-tests-config-sample.php → tests/wp-tests-config.php and fill DB/consts.\n");
        exit(1);
    }
}

$coreWpSettings = $wpPhpUnit . '/wordpress/wp-settings.php';
if (!is_file($coreWpSettings)) {
    fwrite(STDERR, "WordPress core not found at {$wpPhpUnit}/wordpress\n".
        "Run: WP_CORE_DIR=/absolute/path/to/wordpress-develop/build php bin/wp-core-link\n");
    exit(1);
}

// ✅ This loads the WP test framework and boots WordPress, defining ABSPATH.
require $wpPhpUnit . '/includes/bootstrap.php';

if (!defined('AP_TEST_MODE')) {
    define('AP_TEST_MODE', 1);
}

// Ensure routes are registered for REST tests that assume pre-registration.
if (function_exists('do_action')) {
    do_action('rest_api_init');
}

// Start unauthenticated by default (tests can set users explicitly).
if (function_exists('wp_set_current_user')) {
    wp_set_current_user(0);
}
