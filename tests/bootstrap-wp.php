<?php
/**
 * WordPress PHPUnit bootstrap for ArtPulse.
 *
 * Requirements:
 * - WP core linked at: vendor/wp-phpunit/wp-phpunit/wordpress (use: php bin/wp-core-link)
 * - Env:
 *     WP_PHPUNIT__DIR            => <repo>/vendor/wp-phpunit/wp-phpunit
 *     WP_PHPUNIT__TESTS_CONFIG   => <repo>/tests/wp-tests-config.php
 * - DO NOT define ABSPATH anywhere in this repo. The WP bootstrap will.
 */

declare(strict_types=1);

// --- Resolve paths and required envs ---
$PLUGIN_ROOT = dirname(__DIR__);
$WP_PHPUNIT  = getenv('WP_PHPUNIT__DIR') ?: $PLUGIN_ROOT . '/vendor/wp-phpunit/wp-phpunit';
$TESTS_CFG   = getenv('WP_PHPUNIT__TESTS_CONFIG') ?: $PLUGIN_ROOT . '/tests/wp-tests-config.php';

if (!is_dir($WP_PHPUNIT)) {
    fwrite(STDERR, "WP_PHPUNIT__DIR missing: {$WP_PHPUNIT}\n");
    exit(1);
}
if (!is_file($TESTS_CFG)) {
    fwrite(STDERR, "Missing tests config at: {$TESTS_CFG}\n");
    fwrite(STDERR, "Copy tests/wp-tests-config-sample.php to tests/wp-tests-config.php and fill DB creds.\n");
    exit(1);
}

// Make sure AP test-only codepaths are active *before* WP boots.
if (!defined('AP_TEST_MODE')) {
    define('AP_TEST_MODE', 1);
}

// --- Load WP testing helpers first (lets us hook plugin load) ---
require_once $WP_PHPUNIT . '/includes/functions.php';

// Load Composer autoload early so classmaps exist even before plugin main is required.
$autoload = $PLUGIN_ROOT . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Tell WordPress how to load this plugin in the test environment.
// We try common entrypoints; adjust if your main plugin file differs.
tests_add_filter('muplugins_loaded', function () use ($PLUGIN_ROOT): void {
    $candidates = [
        $PLUGIN_ROOT . '/art-test-main.php',
        $PLUGIN_ROOT . '/artpulse.php',
        $PLUGIN_ROOT . '/plugin.php',
        // as a fallback, your plugin may self-register on autoload/init only
    ];
    foreach ($candidates as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// Now bootstrap WordPress (this DEFINES ABSPATH and loads vendor wp-settings.php)
require_once $WP_PHPUNIT . '/includes/bootstrap.php';

// Optional sanity checks (helpful when debugging ABSPATH issues)
if (!defined('ABSPATH')) {
    fwrite(STDERR, "ERROR: ABSPATH not defined after WP bootstrap.\n");
    exit(1);
}
if (!file_exists(ABSPATH . 'wp-settings.php')) {
    fwrite(STDERR, "ERROR: wp-settings.php not found at ABSPATH: " . ABSPATH . "\n");
    fwrite(STDERR, "Did you run: WP_CORE_DIR=/path/to/wordpress php bin/wp-core-link ?\n");
    exit(1);
}

// Preload REST routes to avoid 404s in tests that assume routes are registered.
do_action('init');
do_action('rest_api_init');

// If your tests need an admin context by default:
if (!function_exists('wp_set_current_user')) {
    require_once ABSPATH . 'wp-includes/pluggable.php';
}
wp_set_current_user(1);
