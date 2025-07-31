<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
require_once __DIR__ . '/translation-helper.php';

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) {}
}

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../artpulse-management.php');
}

require_once __DIR__ . '/../includes/dashboard-builder-widgets.php';
ap_register_dashboard_builder_widget_map();

$plugin_dir = dirname(__DIR__);

global $ap_widget_source_map, $ap_widget_status;

$manifest = [];

foreach ($ap_widget_source_map as $role => $widgets) {
    foreach ($widgets as $id => $file) {
        $path = '';
        if (file_exists($plugin_dir . '/widgets/' . $file)) {
            $path = 'widgets/' . $file;
        } elseif (file_exists($plugin_dir . '/assets/js/widgets/' . $file)) {
            $path = 'assets/js/widgets/' . $file;
        }
        $status = in_array($file, $ap_widget_status['missing'], true) ? 'missing' : 'registered';
        if (!isset($manifest[$id])) {
            $manifest[$id] = [
                'file' => $path,
                'roles' => [$role],
                'status' => $status,
            ];
        } else {
            $manifest[$id]['roles'][] = $role;
            $manifest[$id]['roles'] = array_values(array_unique($manifest[$id]['roles']));
            if ($status === 'missing') {
                $manifest[$id]['status'] = 'missing';
            }
        }
    }
}

foreach ($ap_widget_status['unregistered'] as $file) {
    $id = pathinfo($file, PATHINFO_FILENAME);
    $path = file_exists($plugin_dir . '/widgets/' . $file) ? 'widgets/' . $file : 'assets/js/widgets/' . $file;
    $manifest[$id] = [
        'file' => $path,
        'roles' => [],
        'status' => 'unregistered',
    ];
}

file_put_contents($plugin_dir . '/widget-manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

printf("Generated %s\n", $plugin_dir . '/widget-manifest.json');
