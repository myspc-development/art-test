<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
}
if (!function_exists('__')) { function __($s){ return $s; } }
if (!function_exists('esc_attr')) { function esc_attr($v){ return $v; } }
if (!function_exists('esc_url_raw')) { function esc_url_raw($v){ return $v; } }
if (!function_exists('wp_create_nonce')) { function wp_create_nonce($v=''){ return 'nonce'; } }
if (!function_exists('rest_url')) { function rest_url($p=''){ return 'http://example.test/' . ltrim($p, '/'); } }
if (!function_exists('add_action')) { function add_action(...$a){} }
if (!function_exists('do_action')) { function do_action(...$a){} }
if (!function_exists('wp_add_dashboard_widget')) { function wp_add_dashboard_widget(...$a){} }
if (!function_exists('get_option')) { function get_option($k,$d=null){ return $d; } }
if (!function_exists('get_current_user_id')) { function get_current_user_id(){ return 1; } }
if (!function_exists('get_user_meta')) { function get_user_meta($id,$k,$single=false){ return []; } }
if (!function_exists('get_users')) { function get_users($a){ return []; } }
if (!function_exists('get_user_by')) { function get_user_by($f,$v){ return (object)['display_name'=>'User']; } }
if (!function_exists('date_i18n')) { function date_i18n($f,$ts){ return date($f,$ts); } }
if (!function_exists('plugin_dir_path')) { function plugin_dir_path($f){ return dirname($f).'/'; } }
if (!function_exists('locate_template')) { function locate_template($t){ return false; } }
if (!function_exists('submit_button')) { function submit_button(){ } }
if (!function_exists('wp_nonce_field')) { function wp_nonce_field(){ } }
if (!defined('ABSPATH')) { define('ABSPATH', __DIR__.'/../'); }
if (!defined('ARTPULSE_PLUGIN_FILE')) { define('ARTPULSE_PLUGIN_FILE', __DIR__.'/../artpulse-management.php'); }

require_once __DIR__ . '/../widgets/stubs.php';
require_once __DIR__ . '/../includes/dashboard-builder-widgets.php';
\ArtPulse\Core\DashboardWidgetRegistry::init();
ap_register_dashboard_builder_widget_map();
ap_register_builder_core_placeholders();

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry as BuilderRegistry;
use ArtPulse\Core\DashboardWidgetRegistry as CoreRegistry;

$builder_defs = BuilderRegistry::get_all();
$core_defs = CoreRegistry::get_all();
$map = include __DIR__ . '/../src/Rest/widget-id-map.php';
$flip_map = array_flip($map);

$roles = ['member','artist','organization'];

$rows = [];
foreach ($roles as $role) {
    $builder_role = BuilderRegistry::get_for_role($role);
    $builder_ids = array_keys($builder_role);
    $core_role = CoreRegistry::get_widgets_by_role($role);
    $core_ids = array_keys($core_role);

    $checked = [];
    foreach ($builder_ids as $bid) {
        $cid = $map[$bid] ?? $bid;
        $has_core = isset($core_defs[$cid]);
        $callback = $has_core ? $core_defs[$cid]['callback'] : null;
        $placeholder = $callback === [CoreRegistry::class, 'render_widget_fallback'] || !is_callable($callback);
        $rows[] = [
            'role' => $role,
            'widget' => $bid,
            'core' => $has_core ? $cid : '—',
            'builder' => '✅',
            'render' => $has_core && !$placeholder ? '✅' : '❌',
            'notes' => $has_core ? ($placeholder ? 'Placeholder only' : '') : 'Missing in registry',
            'fix' => $has_core ? ($placeholder ? 'Implement real callback in registry' : '—') : 'Register in DashboardWidgetRegistry'
        ];
        $checked[$cid] = true;
    }

    foreach ($core_ids as $cid) {
        if (isset($checked[$cid])) continue;
        $bid = $flip_map[$cid] ?? null;
        $in_builder = $bid && in_array($bid, $builder_ids, true);
        $callback = $core_defs[$cid]['callback'];
        $placeholder = $callback === [CoreRegistry::class, 'render_widget_fallback'] || !is_callable($callback);
        $rows[] = [
            'role' => $role,
            'widget' => $bid ?? $cid,
            'core' => $cid,
            'builder' => $in_builder ? '✅' : '❌',
            'render' => !$placeholder ? '✅' : '❌',
            'notes' => ($bid ? '' : 'Unmapped') . ($in_builder ? '' : ($bid ? '' : '; Missing in builder')) . ($placeholder ? '; Placeholder only' : ''),
            'fix' => (!$bid ? 'Add to ID map. ' : '') . (!$in_builder ? 'Add to builder map and registry roles. ' : '') . ($placeholder ? 'Implement real callback' : '') ?: '—'
        ];
    }
}

usort($rows, static function($a,$b){ return [$a['role'],$a['widget']] <=> [$b['role'],$b['widget']]; });

$header = ["Role","Widget ID","Core ID","In Builder","Has Render Function","Notes","Fix Suggestion"];
printf("%s\n", implode("\t", $header));
foreach ($rows as $r) {
    printf("%s\t%s\t%s\t%s\t%s\t%s\t%s\n", $r['role'], $r['widget'], $r['core'], $r['builder'], $r['render'], $r['notes'], $r['fix']);
}
