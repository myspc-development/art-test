<?php
declare(strict_types=1);

// --- WP core local fallback: if vendor wordpress missing, symlink or fail with a clear message.
$__tests_dir = getenv('WP_PHPUNIT__DIR') ?: __DIR__ . '/../vendor/wp-phpunit/wp-phpunit';
$__vendor_wp = $__tests_dir . '/wordpress/wp-settings.php';

if (!file_exists($__vendor_wp)) {
    $local = getenv('WP_CORE_DIR'); // e.g. /www/wwwroot/192.168.1.21
    if ($local && file_exists(rtrim($local, '/').'/wp-settings.php')) {
        $target = $__tests_dir . '/wordpress';
        if (!is_dir($target)) {
            @mkdir(dirname($target), 0777, true);
        }
        // Try to symlink the real WP install into vendor/wp-phpunit/wp-phpunit/wordpress
        if (!is_dir($target) || !file_exists($target.'/wp-settings.php')) {
            @unlink($target);
            if (@symlink($local, $target) === false) {
                // As a last resort, stop here with guidance
                fwrite(STDERR, "Could not create wordpress symlink. Set write perms or pre-create: ln -s {$local} {$target}\n");
                exit(1);
            }
        }
    } else {
        fwrite(STDERR, "WordPress core not found. Set WP_CORE_DIR to an existing WP root and run `composer run wp:core-link`.\n");
        fwrite(STDERR, "Example: export WP_CORE_DIR=/www/wwwroot/192.168.1.21 && composer run wp:core-link\n");
        exit(1);
    }
}

$_tests_dir = $__tests_dir;
if (!file_exists($_tests_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WP PHPUnit not found at {$_tests_dir}\n");
    exit(1);
}

$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Load widget seeding utilities early so hooks can be registered before plugin boot.
require_once __DIR__ . '/Support/SeedWidgets.php';
set_error_handler(
    static function (int $errno, string $errstr): bool {
        if (\ArtPulse\Tests\mute_missing_widget_warning($errno, $errstr)) {
            return true;
        }
        return false;
    },
    E_USER_WARNING
);

// Flag that tests are running so plugin code can relax certain checks.
if (!defined('AP_TESTING')) {
    define('AP_TESTING', true);
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
if (function_exists('\\ArtPulse\\Tests\\ap_seed_dashboard_widgets_bootstrap')) {
    tests_add_filter('muplugins_loaded', '\\ArtPulse\\Tests\\ap_seed_dashboard_widgets_bootstrap', 5);
}
if (function_exists('ap_register_rest_profile_update_shim')) {
    tests_add_filter('rest_api_init', 'ap_register_rest_profile_update_shim', 1);
}
if (function_exists('\\ArtPulse\\Tests\\ap_tests_boot_rest_defaults')) {
    tests_add_filter('init', '\\ArtPulse\\Tests\\ap_tests_boot_rest_defaults', 20);
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
