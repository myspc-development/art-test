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
 * Load test helper traits/utilities (AjaxTestHelper, factories, etc).
 */
foreach ([__DIR__.'/Traits', __DIR__.'/Support', __DIR__.'/helpers'] as $dir) {
    if (is_dir($dir)) {
        foreach (glob($dir.'/*.php') as $file) {
            require_once $file;
        }
    }
}

/**
 * Load the plugin under test (try common entrypoints).
 */
function _manually_load_plugin() {
    $root = dirname(__DIR__);
    foreach (['/artpulse-management.php','/artpulse.php'] as $rel) {
        $path = $root . $rel;
        if (file_exists($path)) { require_once $path; return; }
    }
    fwrite(STDERR, "Could not find plugin main file.\n");
}

require $_tests_dir . '/includes/functions.php';
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require $_tests_dir . '/includes/bootstrap.php';
