<?php
declare(strict_types=1);

$_tests_dir      = dirname(__DIR__);
$wp_phpunit_dir  = $_tests_dir . '/vendor/wp-phpunit/wp-phpunit';
$local_core      = $wp_phpunit_dir . '/wordpress/wp-settings.php';
if (!file_exists($local_core)) {
    fwrite(
        STDERR,
        "WordPress test suite not linked.\n" .
        "1) export WP_CORE_DIR=/absolute/path/to/wordpress\n" .
        "2) composer run wp:core-link\n"
    );
    exit(1);
}
if (!file_exists($wp_phpunit_dir . '/includes/functions.php')) {
    fwrite(STDERR, "WP PHPUnit not found at {$wp_phpunit_dir}\n");
    exit(1);
}

$autoloader = $_tests_dir . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

require_once __DIR__ . '/Support/ErrorSilencer.php';

if (!defined('WP_PHPUNIT__TESTS_CONFIG')) {
    define('WP_PHPUNIT__TESTS_CONFIG', $_tests_dir . '/wp-tests-config.php');
}

set_error_handler([\ArtPulse\Tests\ErrorSilencer::class, 'muteMissingWidgetWarning'], E_USER_WARNING);

// Flag that tests are running so plugin code can relax certain checks.
if (!defined('AP_TESTING')) {
    define('AP_TESTING', true);
}

if (!defined('AP_TEST_MODE')) {
    define('AP_TEST_MODE', true);
}
// Optionally force builder preview mode in tests via env var.
$__force_preview = getenv('AP_TEST_FORCE_PREVIEW');
if ($__force_preview !== false && !defined('AP_TEST_FORCE_PREVIEW')) {
    define('AP_TEST_FORCE_PREVIEW', (bool) $__force_preview);
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
    require_once $wp_phpunit_dir . '/includes/functions.php';
}

/**
 * Schedule test-only helpers BEFORE plugin boot (or at REST init).
 * Lower priority runs earlier.
 */
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\DashboardDefinitionSanitizer::class, 'register'], 4);
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\SeedWidgets::class, 'bootstrap'], 5);
tests_add_filter('rest_api_init', [\ArtPulse\Tests\RestProfileUpdateShim::class, 'register'], 1);

/** Now load the plugin */
tests_add_filter('muplugins_loaded', '_manually_load_plugin', 15);

/** Finally, boot the WP test environment (guarded, load once). */
require_once $wp_phpunit_dir . '/includes/bootstrap.php';

remove_action('user_register', [\ArtPulse\Core\DashboardWidgetManager::class, 'assign_default_layout']);

if (!defined('AP_TEST_MODE')) {
    define('AP_TEST_MODE', true);
}

if ( defined('AP_TEST_MODE') && AP_TEST_MODE ) {
    if (!class_exists('Spy_REST_Server')) {
        require_once $wp_phpunit_dir . '/includes/spy-rest-server.php';
    }
    $GLOBALS['wp_rest_server'] = new \Spy_REST_Server();

    tests_add_filter('init', static function () {
        do_action('rest_api_init');

        $admin_id = username_exists('ap-test-admin');
        if (!$admin_id) {
            $admin_id = wp_insert_user([
                'user_login' => 'ap-test-admin',
                'user_pass'  => wp_generate_password(12, false),
                'user_email' => 'ap-test-admin@example.com',
                'role'       => 'administrator',
            ]);
        }

        if (!is_wp_error($admin_id)) {
            wp_set_current_user($admin_id);
        }

        $ref = new \ReflectionClass( \ArtPulse\Core\DashboardWidgetRegistry::class );
        foreach (['aliases'] as $prop) {
            if ($ref->hasProperty($prop)) {
                $p = $ref->getProperty($prop);
                $p->setAccessible(true);
                $p->setValue(null, []);
            }
        }
    });

    tests_add_filter(
        'rest_pre_dispatch',
        static function ($result, $server, $request) {
            if ($server instanceof \WP_REST_Server && empty($server->get_routes())) {
                do_action('rest_api_init', $server);
            }
            return $result;
        },
        10,
        3
    );
}

// Run after seeding (5) but before plugin load (15); 6 is fine.
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\WidgetRolesOverlay::class, 'register'], 6);
// After seeding (5) but before plugin load (15) so hooks are ready for the test's update_option()
tests_add_filter('muplugins_loaded', [\ArtPulse\Tests\WidgetRolesApplyOnUpdate::class, 'register'], 6);
