<?php
// tests/bootstrap.php

// 1) Composer autoload (optional libs)
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}
require_once dirname(__DIR__) . '/vendor/antecedent/patchwork/Patchwork.php';

// 2) Locate the WordPress PHPUnit bootstrap
$wp_phpunit_dir = getenv('WP_PHPUNIT__DIR');

if (!$wp_phpunit_dir) {
    // Prefer vendor-installed wp-phpunit
    $vendor_wp = dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';
    if (file_exists($vendor_wp . '/includes/bootstrap.php')) {
        $wp_phpunit_dir = $vendor_wp;
    }
}

// Try a local wordpress-develop checkout if still not found
$dev_bootstrap = getenv('WP_DEVELOP_DIR')
    ? rtrim(getenv('WP_DEVELOP_DIR'), '/\\') . '/tests/phpunit/includes/bootstrap.php'
    : null;

// Load the WP test bootstrap
$wp_root        = '';
$bootstrap_file = '';

if ($wp_phpunit_dir && file_exists($wp_phpunit_dir . '/includes/bootstrap.php')) {
    $bootstrap_file = $wp_phpunit_dir . '/includes/bootstrap.php';

    $candidates   = [];
    $candidates[] = dirname($wp_phpunit_dir);
    $candidates[] = $wp_phpunit_dir . '/wordpress';

    if (($env = getenv('WP_CORE_DIR')) !== false) {
        $candidates[] = rtrim($env, '/');
    }

    if (($dev = getenv('WP_DEVELOP_DIR')) !== false) {
        $dev          = rtrim($dev, '/\\');
        $candidates[] = $dev . '/src';
        $candidates[] = $dev . '/build';
    }

    foreach ($candidates as $candidate) {
        if ($candidate !== '' && file_exists($candidate . '/wp-settings.php')) {
            $wp_root = rtrim($candidate, '/');
            break;
        }
    }
} elseif (defined('ABSPATH') && file_exists(ABSPATH . 'tests/phpunit/includes/bootstrap.php')) {
    // ABSPATH may be defined by WordPress' wp-tests-config.php
    $wp_root        = rtrim(ABSPATH, '/');
    $bootstrap_file = ABSPATH . 'tests/phpunit/includes/bootstrap.php';
} else {
    fwrite(STDERR, "ERROR: Could not locate WordPress PHPUnit bootstrap.\n" .
                   "Set WP_PHPUNIT__DIR or install wp-phpunit in vendor.\n");
    exit(1);
}

// Load WP test functions early so we can hook before WordPress boots
if ($wp_phpunit_dir && file_exists($wp_phpunit_dir . '/includes/functions.php')) {
    require_once $wp_phpunit_dir . '/includes/functions.php';
}

// 3) Load the plugin under test once WordPress loads mu-plugins
tests_add_filter('muplugins_loaded', function () {
    $plugin_root = dirname(__DIR__);
    $plugin_file = $plugin_root . '/artpulse-management.php';

    if (! defined('ARTPULSE_PLUGIN_FILE')) {
        define('ARTPULSE_PLUGIN_FILE', $plugin_file);
    }

    require_once $plugin_file;
});

// Now bootstrap WordPress
require_once $bootstrap_file;

// 4) Load shared stubs AFTER WP test environment is initialized
require_once __DIR__ . '/TestStubs.php';
require_once __DIR__ . '/Frontend/_stubs.php';
require_once __DIR__ . '/Rest/helpers.php';
