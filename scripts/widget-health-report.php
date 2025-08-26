<?php
// Generates a dashboard widget health report.

// Ensure basic WordPress-style constants exist so widget files do not exit.
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}
if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../artpulse-management.php');
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/widget-loader.php';
require_once __DIR__ . '/translation-helper.php';

// Suppress PHP warnings from missing WordPress environment.
error_reporting(E_ERROR | E_PARSE);

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;

// -----------------------------------------------------------------------------
// Minimal stubs for common WordPress functions when running via CLI.
// -----------------------------------------------------------------------------
if (!function_exists('__')) {
    function __($text, $domain = null) { return $text; }
}
if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = null) { return $text; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return $text; }
}
if (!function_exists('esc_attr')) {
    function esc_attr($text) { return $text; }
}
if (!function_exists('esc_url')) {
    function esc_url($url) { return $url; }
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) { return $value; }
}
if (!function_exists('do_action')) {
    function do_action($tag, ...$args) {}
}
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) {}
}
if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return is_scalar($str) ? (string) $str : ''; }
}
if (!function_exists('get_option')) {
    function get_option($key, $default = []) { return $default; }
}
if (!function_exists('get_current_user_id')) {
    function get_current_user_id() { return 0; }
}
if (!function_exists('user_can')) {
    function user_can($user_id, $cap) { return true; }
}
if (!function_exists('current_user_can')) {
    function current_user_can($cap) { return true; }
}
if (!function_exists('get_userdata')) {
    function get_userdata($user_id) { return (object) ['roles' => ['member']]; }
}
if (!function_exists('wp_roles')) {
    function wp_roles() {
        return (object) ['roles' => [
            'administrator' => [],
            'editor'        => [],
            'author'        => [],
            'contributor'   => [],
            'subscriber'    => [],
            'member'        => [],
            'artist'        => [],
            'organization'  => [],
        ]];
    }
}

// Register widgets so the registry has definitions.
DashboardWidgetRegistry::init();
$registry = DashboardWidgetRegistry::get_all();

$roles = array_keys(wp_roles()->roles);
$rows  = [];
foreach ($roles as $role) {
    $layout = UserLayoutManager::get_role_layout($role);
    foreach ($layout as $item) {
        $id = $item['id'] ?? '';
        if (!$id) {
            continue;
        }
        $registered = isset($registry[$id]);
        $callable   = false;
        $rendered   = false;
        if ($registered) {
            $callback = $registry[$id]['callback'] ?? null;
            $callable = is_callable($callback);
            if ($callable) {
                ob_start();
                try {
                    call_user_func($callback, 0);
                    $rendered = true;
                } catch (\Throwable $e) {
                    $rendered = false;
                }
                ob_end_clean();
            }
        }
        $status = ($registered && $callable && $rendered) ? 'OK' : 'error';
        $rows[] = [
            'role'       => $role,
            'id'         => $id,
            'registered' => $registered,
            'callable'   => $callable,
            'rendered'   => $rendered,
            'status'     => $status,
        ];
    }
}

$lines   = [];
$lines[] = sprintf("%-15s %-30s %-10s %-10s %-10s %-6s", 'Role', 'Widget ID', 'Registered', 'Callable', 'Rendered', 'Status');
foreach ($rows as $row) {
    $lines[] = sprintf(
        "%-15s %-30s %-10s %-10s %-10s %-6s",
        $row['role'],
        $row['id'],
        $row['registered'] ? 'yes' : 'no',
        $row['callable'] ? 'yes' : 'no',
        $row['rendered'] ? 'yes' : 'no',
        $row['status']
    );
}
$output = implode(PHP_EOL, $lines) . PHP_EOL;

echo $output;

if ($argc > 1) {
    $path = $argv[1];
    file_put_contents($path, $output);
}
