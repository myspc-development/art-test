<?php
// tests/bootstrap.php — WordPress-less test bootstrap for PHPUnit + Brain Monkey.
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// 1) Load Composer autoload from common locations
$candidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];
$autoload = null;
foreach ($candidates as $try) {
    if (is_file($try)) { $autoload = $try; break; }
}
if (!$autoload) {
    fwrite(STDERR, "[bootstrap] Composer autoload not found. Run `composer install` in plugin root.\n");
    exit(1);
}
require_once $autoload;

// 2) If dev autoload omitted Brain Monkey, require its sources explicitly
$bmSrc = null;
if (class_exists(\Composer\InstalledVersions::class) && \Composer\InstalledVersions::isInstalled('brain/monkey')) {
    $bmPath = \Composer\InstalledVersions::getInstallPath('brain/monkey');
    if ($bmPath && is_dir($bmPath . '/src')) { $bmSrc = $bmPath . '/src'; }
}
if (!$bmSrc) {
    $maybe = realpath(__DIR__ . '/../vendor/brain/monkey/src');
    if ($maybe && is_dir($maybe)) { $bmSrc = $maybe; }
}
if (!function_exists('Brain\\Monkey\\Functions\\when') && $bmSrc) {
    foreach (['Functions.php','Actions.php','Filters.php','Expectations.php','Patchers.php','Monkey.php'] as $file) {
        $full = $bmSrc . '/' . $file;
        if (is_file($full)) require_once $full;
    }
}
if (!function_exists('Brain\\Monkey\\Functions\\when')) {
    fwrite(STDERR, "[bootstrap] Brain\\Monkey not found (namespace functions missing). Re-run: COMPOSER_NO_DEV=0 composer install\n");
    exit(1);
}

// Ensure Patchwork is loaded before defining polyfills so functions can be redefined
if (is_file(__DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php')) {
    require_once __DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php';
}

// 3) Minimal constants/polyfills used by code under test
if (!defined('ARTPULSE_PLUGIN_FILE')) define('ARTPULSE_PLUGIN_FILE', __FILE__);
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($s){ return preg_replace('/[^A-Za-z0-9_\-]/','',strip_tags((string)$s)); } }
if (!function_exists('wp_unslash'))          { function wp_unslash($v){ return $v; } }
if (!function_exists('esc_url_raw'))         { function esc_url_raw($u){ return filter_var((string)$u, FILTER_SANITIZE_URL); } }
if (!function_exists('admin_url'))           { function admin_url($p = ''){ return 'http://example.com/wp-admin/' . ltrim($p,'/'); } }
if (!function_exists('rest_url'))            { function rest_url($p = ''){ return 'http://example.com/wp-json/' . ltrim($p,'/'); } }
if (!function_exists('__'))                  { function __($t){ return $t; } }
if (!function_exists('esc_html__'))          { function esc_html__($t){ return $t; } }
require_once __DIR__ . '/polyfills.php';

