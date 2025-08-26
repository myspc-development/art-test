<?php
// tests/bootstrap.php — WordPress-less test bootstrap for PHPUnit + Brain Monkey.
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/** 1) Find Composer autoload */
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
    fwrite(STDERR, "[bootstrap] No composer autoload found. Run composer install in plugin root.\n");
    exit(1);
}
require_once $autoload;
fwrite(STDERR, "[bootstrap] autoload: {$autoload}\n");

/** 2) Safety net: if dev autoload missed Brain Monkey, require its sources directly */
if (!class_exists(\Brain\Monkey\Functions::class)) {
    $bm = realpath(__DIR__ . '/../vendor/brain/monkey/src');
    fwrite(STDERR, "[bootstrap] Brain\\Monkey autoloaded? NO. Fallback src=" . ($bm ?: 'N/A') . "\n");
    if ($bm && is_dir($bm)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($bm));
        foreach ($it as $file) {
            if ($file->isFile() && substr($file->getFilename(), -4) === '.php') {
                require_once $file->getPathname();
            }
        }
    }
}
if (!class_exists(\Brain\Monkey\Functions::class)) {
    fwrite(STDERR, "[bootstrap] Brain\\Monkey still missing. Re-run: COMPOSER_NO_DEV=0 composer install\n");
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
if (!function_exists('plugin_dir_path'))     { function plugin_dir_path($f){ return rtrim(dirname((string)$f),'/\\').'/'; } }
if (!function_exists('plugin_dir_url'))      { function plugin_dir_url($f){ return 'https://example.test/plugin/'; } }
