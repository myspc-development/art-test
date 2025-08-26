<?php
// tests/bootstrap.php — WordPress-less test bootstrap for PHPUnit + Brain Monkey.
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/** 1) Composer autoload (search common locations) */
$candidates = [
    __DIR__ . '/../vendor/autoload.php',          // plugin/vendor (expected)
    __DIR__ . '/../../vendor/autoload.php',       // wp-content/plugins
    __DIR__ . '/../../../vendor/autoload.php',    // wp-content
    __DIR__ . '/../../../../vendor/autoload.php', // project root
];
$autoload = null;
foreach ($candidates as $try) {
    if (is_file($try)) { $autoload = $try; break; }
}
if (!$autoload) {
    fwrite(STDERR, "[bootstrap] Composer autoload not found. Run `composer install` in the plugin root.\n");
    exit(1);
}
require_once $autoload;

/** 2) Force-load Brain Monkey sources if dev autoload didn’t include them */
if (!function_exists('Brain\\Monkey\\Functions\\when')) {
    $bm = __DIR__ . '/../vendor/brain/monkey/src';
    // Explicit file list for BM 2.x
    $files = [
        $bm . '/Functions.php',
        $bm . '/Actions.php',
        $bm . '/Filters.php',
        $bm . '/Expectations.php',
        $bm . '/Patchers.php',
        $bm . '/Monkey.php', // defines Brain\Monkey\setUp/tearDown
    ];
    foreach ($files as $f) {
        if (is_file($f)) { require_once $f; }
    }
}
if (!function_exists('Brain\\Monkey\\Functions\\when')) {
    fwrite(STDERR, "[bootstrap] Brain\\Monkey not found (namespace functions missing). Re-run: COMPOSER_NO_DEV=0 composer install\n");
    exit(1);
}

/** 3) Minimal constants/polyfills used by code under test */
if (!defined('ARTPULSE_PLUGIN_FILE')) define('ARTPULSE_PLUGIN_FILE', __FILE__);

if (!function_exists('sanitize_text_field')) { function sanitize_text_field($s){ return preg_replace('/[^A-Za-z0-9_\-]/','',strip_tags((string)$s)); } }
if (!function_exists('wp_unslash'))          { function wp_unslash($v){ return $v; } }
if (!function_exists('esc_url_raw'))         { function esc_url_raw($u){ return (string)$u; } }
if (!function_exists('admin_url'))           { function admin_url($p=''){ return 'https://example.test/wp-admin/'.ltrim((string)$p,'/'); } }
if (!function_exists('rest_url'))            { function rest_url($p=''){ return 'https://example.test/wp-json/'.ltrim((string)$p,'/'); } }
if (!function_exists('__'))                  { function __($t,$d=null){ return (string)$t; } }
if (!function_exists('esc_html__'))          { function esc_html__($t,$d=null){ return (string)$t; } }
// Plugin path helpers are stubbed per-test when needed to avoid conflicts
// with Brain Monkey's function patching.
