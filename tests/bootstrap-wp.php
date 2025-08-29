<?php
declare(strict_types=1);

$_tests_dir = getenv('WP_PHPUNIT__DIR');
if (!$_tests_dir) {
    $_tests_dir = __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
}
if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WP PHPUnit not found at {$_tests_dir}\n");
    exit(1);
}

$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

/**
 * Load test helper traits/utilities (AjaxTestHelper, factories, stubs, etc).
 * IMPORTANT: these files must NOT call add_filter()/add_action() at load time.
 * Register hooks via functions we schedule below.
 */
foreach ([__DIR__ . '/Traits', __DIR__ . '/Support', __DIR__ . '/helpers'] as $dir) {
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
if (function_exists('ap_register_dashboard_definition_sanitizer')) {
    tests_add_filter('muplugins_loaded', 'ap_register_dashboard_definition_sanitizer', 4);
}
if (function_exists('ap_seed_dashboard_widgets_bootstrap')) {
    tests_add_filter('muplugins_loaded', 'ap_seed_dashboard_widgets_bootstrap', 5);
}
if (function_exists('ap_register_rest_profile_update_shim')) {
    tests_add_filter('rest_api_init', 'ap_register_rest_profile_update_shim', 1);
}

/** Now load the plugin */
tests_add_filter('muplugins_loaded', '_manually_load_plugin', 15);

/** Finally, boot the WP test environment (guarded, load once). */
require_once $_tests_dir . '/includes/bootstrap.php';

if (function_exists('ap_register_widget_roles_overlay')) {
    // Run after seeding (5) but before plugin load (15); 6 is fine.
    tests_add_filter('muplugins_loaded', 'ap_register_widget_roles_overlay', 6);
}

if (function_exists('ap_register_widget_roles_apply_on_update')) {
    // After seeding (5) but before plugin load (15) so hooks are ready for the test's update_option()
    tests_add_filter('muplugins_loaded', 'ap_register_widget_roles_apply_on_update', 6);
}
