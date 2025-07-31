<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/translation-helper.php';
if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __DIR__ . '/../artpulse-management.php');
}

use ArtPulse\Core\DashboardWidgetRegistry;

DashboardWidgetRegistry::init();
$defs = DashboardWidgetRegistry::get_definitions(true);

$docsDir = __DIR__ . '/../docs/widgets';
if (!is_dir($docsDir)) {
    mkdir($docsDir, 0777, true);
}
$date = date('Y-m-d');

foreach ($defs as $id => $def) {
    $roles = isset($def['roles']) ? implode(', ', (array)$def['roles']) : 'all';
    $file = $docsDir . '/' . str_replace('_', '-', $id) . '.md';
    $content = "---\n";
    $content .= "title: {$def['name']}\n";
    $content .= "category: widgets\n";
    $content .= "role: developer\n";
    $content .= "last_updated: {$date}\n";
    $content .= "status: draft\n";
    $content .= "---\n\n";
    $content .= "# {$def['name']}\n\n";
    $content .= "**Widget ID:** `{$id}`\n\n";
    $content .= "**Roles:** {$roles}\n\n";
    $content .= "## Description\n";
    $content .= $def['description'] . "\n\n";
    $content .= "## Notes\n";
    $content .= "This documentation was auto-generated.\n";
    file_put_contents($file, $content);
}
