<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
if (!function_exists('__')) {
    function __($s) { return $s; }
}
if (!function_exists('__return_empty_string')) {
    function __return_empty_string() { return ''; }
}
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) {}
}
if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {}
}
if (!function_exists('get_option')) {
    function get_option($key, $default = null) { return $default; }
}
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) { return dirname($file) . '/'; }
}
if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value) { return $value; }
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}
if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../artpulse-management.php');
}

require_once __DIR__ . '/../includes/dashboard-builder-widgets.php';
ap_register_dashboard_builder_widget_map();

require_once __DIR__ . '/../includes/business-dashboard-widgets.php';
require_once __DIR__ . '/../includes/guide-dashboard-widgets.php';
require_once __DIR__ . '/../includes/dashboard-messages-widget.php';
require_once __DIR__ . '/../includes/dashboard-widgets.php';

\ArtPulse\Core\DashboardWidgetRegistry::init();
ap_register_business_dashboard_widgets();
ap_register_guide_widgets();
ap_register_core_dashboard_widgets();
ap_register_builder_core_placeholders();
// messages widget registers via closure in the include above

$map = \ArtPulse\Core\DashboardWidgetRegistry::get_id_map();
$path = __DIR__ . '/../src/Rest/widget-id-map.php';
file_put_contents($path, "<?php\nreturn " . var_export($map, true) . ";\n");

ksort($map);
foreach ($map as $builder => $core) {
    echo "$builder => $core\n";
}
