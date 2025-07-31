<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
require_once __DIR__ . '/../translation-helper.php';
if (!function_exists('__return_empty_string')) {
    function __return_empty_string() { return ''; }
}
if (!function_exists('add_action')) { function add_action($h,$c,$p=10,$a=1) {} }
if (!function_exists('do_action')) { function do_action($h,...$a) {} }
if (!function_exists('get_option')) { function get_option($k,$d=null){return $d;} }
if (!function_exists('plugin_dir_path')) { function plugin_dir_path($f){return dirname($f).'/';} }
if (!function_exists('apply_filters')) { function apply_filters($h,$v){return $v;} }
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/../'); }
if (!defined('ARTPULSE_PLUGIN_FILE')) { define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../artpulse-management.php'); }

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

$builder_defs = \ArtPulse\DashboardBuilder\DashboardWidgetRegistry::get_all();
$core_defs = \ArtPulse\Core\DashboardWidgetRegistry::get_all();
$map = \ArtPulse\Core\DashboardWidgetRegistry::get_id_map();

printf("%-25s %-25s %-20s %s\n", 'Builder ID', 'Core ID', 'Roles', 'In Both');
foreach ($builder_defs as $bid => $bdef) {
    $core_id = $map[$bid] ?? '(missing)';
    $roles   = implode(',', $bdef['roles'] ?? []);
    $exists  = isset($core_defs[$core_id]) ? 'yes' : 'no';
    printf("%-25s %-25s %-20s %s\n", $bid, $core_id, $roles, $exists);
}
