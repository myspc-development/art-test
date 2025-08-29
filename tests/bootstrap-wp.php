<?php
declare(strict_types=1);

$_tests_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
$local_core = $_tests_dir . '/wordpress/wp-settings.php';
if (!file_exists($local_core)) {
    fwrite(STDERR, "WordPress core not found. Set WP_CORE_DIR and run composer run wp:core-link.\n");
    exit(1);
}
if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WP PHPUnit not found at {$_tests_dir}\n");
    exit(1);
}

$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

set_error_handler([\ArtPulse\Tests\ErrorSilencer::class, 'muteMissingWidgetWarning'], E_USER_WARNING);

// Flag that tests are running so plugin code can relax certain checks.
if (!defined('AP_TESTING')) {
    define('AP_TESTING', true);
}
if (getenv('AP_TEST_MODE') === false) {
    putenv('AP_TEST_MODE=1');
}
// Optionally force builder preview mode in tests via env var.
$__force_preview = getenv('AP_TEST_FORCE_PREVIEW');
if ($__force_preview !== false && !defined('AP_TEST_FORCE_PREVIEW')) {
    define('AP_TEST_FORCE_PREVIEW', (bool) $__force_preview);
}

/**
 * Load test helper traits/utilities (AjaxTestHelper, factories, stubs, etc).
 * IMPORTANT: these files must NOT call add_filter()/add_action() at load time.
 * Register hooks via functions we schedule below.
 */
foreach ([__DIR__ . '/Traits', __DIR__ . '/helpers'] as $dir) {
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.php') as $file) {
            require_once $file;
        }
    }
}

/**
 * Load the plugin under test (try common entrypoints).
 */
function _manually_load_plugin() {
    $root = dirname(__DIR__);
    foreach (['/artpulse-management.php', '/artpulse.php'] as $rel) {
        $path = $root . $rel;
        if (file_exists($path)) { require_once $path; return; }
    }
    fwrite(STDERR, "Could not find plugin main file.\n");
}

/**
 * Load WordPress test helpers (tests_add_filter, etc.) exactly once.
 */
if (!function_exists('tests_add_filter')) {
    require_once $_tests_dir . '/includes/functions.php';
}

/**
 * Schedule test-only helpers BEFORE plugin boot (or at REST init).
 * Lower priority runs earlier.
 */
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\DashboardDefinitionSanitizer::class, 'register'], 4);
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\SeedWidgets::class, 'bootstrap'], 5);
tests_add_filter('rest_api_init', [\ArtPulse\Tests\RestProfileUpdateShim::class, 'register'], 1);
tests_add_filter('init', [\ArtPulse\Tests\RestTestHelpers::class, 'boot'], 20);

/** Now load the plugin */
tests_add_filter('muplugins_loaded', '_manually_load_plugin', 15);

/** Finally, boot the WP test environment (guarded, load once). */
require_once $_tests_dir . '/includes/bootstrap.php';

// Run after seeding (5) but before plugin load (15); 6 is fine.
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\WidgetRolesOverlay::class, 'register'], 6);
// After seeding (5) but before plugin load (15) so hooks are ready for the test's update_option()
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\WidgetRolesApplyOnUpdate::class, 'register'], 6);
