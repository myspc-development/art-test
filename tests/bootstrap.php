<?php
// tests/bootstrap.php — WordPress-less test bootstrap for PHPUnit + Brain Monkey.
declare(strict_types=1);

// Fail fast in tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * 1) Load Composer autoload from common locations
 */
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

/**
 * 2) Safety net: if dev autoload missed Brain Monkey but the package exists,
 *    require its sources directly so tests still run.
 */
if (!class_exists(\Brain\Monkey\Functions::class)) {
    $bm = __DIR__ . '/../vendor/brain/monkey/src';
    if (is_dir($bm)) {
        foreach (['Functions','Actions','Filters','Expectations','Patchers','Monkey'] as $f) {
            $p = $bm . '/' . $f . '.php';
            if (is_file($p)) require_once $p;
        }
    }
}
if (!class_exists(\Brain\Monkey\Functions::class)) {
    fwrite(STDERR, "[bootstrap] Brain\\Monkey not found on the autoload path. Re-run: COMPOSER_NO_DEV=0 composer install\n");
    exit(1);
}

/**
 * 3) Minimal constants/polyfills used by code under test
 */
if (!defined('ARTPULSE_PLUGIN_FILE')) define('ARTPULSE_PLUGIN_FILE', __FILE__);

if (!function_exists('sanitize_text_field')) { function sanitize_text_field($str) { return preg_replace('/[^A-Za-z0-9_\-]/', '', strip_tags((string)$str)); } }
if (!function_exists('wp_unslash'))          { function wp_unslash($value) { return $value; } }
if (!function_exists('esc_url_raw'))         { function esc_url_raw($url) { return (string)$url; } }
if (!function_exists('admin_url'))           { function admin_url($path = '') { return 'https://example.test/wp-admin/' . ltrim((string)$path, '/'); } }
if (!function_exists('rest_url'))            { function rest_url($path = '') { return 'https://example.test/wp-json/' . ltrim((string)$path, '/'); } }
if (!function_exists('__'))                  { function __($text, $domain = null) { return (string)$text; } }
if (!function_exists('esc_html__'))          { function esc_html__($text, $domain = null) { return (string)$text; } }
if (!function_exists('plugin_dir_path'))     { function plugin_dir_path($file) { return rtrim(dirname((string)$file), '/\\') . '/'; } }
if (!function_exists('plugin_dir_url'))      { function plugin_dir_url($file) { return 'https://example.test/plugin/'; } }
