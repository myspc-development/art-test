<?php
// tests/bootstrap.php
// WordPress-less test bootstrap for PHPUnit + Brain Monkey.

declare(strict_types=1);

// 1) Error reporting (fail fast during tests)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 2) Composer autoload (try typical locations)
$autoloads = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
];
$loaded = false;
foreach ($autoloads as $a) {
    if (file_exists($a)) { require_once $a; $loaded = true; break; }
}
if (!$loaded) {
    fwrite(STDERR, "Composer autoload not found. Run `composer install`.\n");
    exit(1);
}

// 3) Minimal plugin constant so plugin_dir_path/url fallbacks work
if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __FILE__);
}

// 4) Lightweight WP polyfills used in code under test.
//    NOTE: Brain Monkey will happily override these in your tests.
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return preg_replace('/[^A-Za-z0-9_\-]/', '', strip_tags((string) $str));
    }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($value) { return $value; }
}
if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url) { return (string) $url; }
}
if (!function_exists('admin_url')) {
    function admin_url($path = '') { return 'https://example.test/wp-admin/' . ltrim((string) $path, '/'); }
}
if (!function_exists('rest_url')) {
    function rest_url($path = '') { return 'https://example.test/wp-json/' . ltrim((string) $path, '/'); }
}
if (!function_exists('__')) {
    function __($text, $domain = null) { return (string) $text; }
}
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = null) { return (string) $text; }
}
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) { return rtrim(dirname((string) $file), '/\\') . '/'; }
}
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) { return 'https://example.test/plugin/'; }
}
