<?php
if (!defined('ABSPATH')) {
    exit;
}

use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Dashboard\WidgetGuard;

/**
 * Ensure member dashboard layout widgets are registered.
 */
function ap_member_dashboard_bootstrap(): void {
    $layout  = UserLayoutManager::get_role_layout('member');
    $widgets = DashboardWidgetRegistry::get_widgets('member');

    foreach ($layout as $entry) {
        $id = sanitize_key($entry['id'] ?? '');
        if (!$id || !empty($widgets[$id])) {
            continue;
        }
        if (ap_member_load_widget($id)) {
            $widgets = DashboardWidgetRegistry::get_widgets('member');
            if (!empty($widgets[$id])) {
                continue;
            }
        }

        $widgets = DashboardWidgetRegistry::get_widgets('member');
    }

    // Rebuild layout so newly registered widgets appear.
    $layout = UserLayoutManager::get_role_layout('member');
    UserLayoutManager::save_role_layout('member', $layout);
    WidgetGuard::validate_and_patch('member');
}

/**
 * Attempt to load an implementation for a widget ID.
 */
function ap_member_load_widget(string $id): bool {
    $base  = plugin_dir_path(ARTPULSE_PLUGIN_FILE);
    $files = glob($base . 'widgets/**/*.php');
    if ($files === false) {
        $files = [];
    }
    $files[] = $base . 'includes/dashboard-widgets.php';

    foreach ($files as $file) {
        if (!is_file($file)) {
            continue;
        }
        $contents = file_get_contents($file);
        if ($contents !== false && (str_contains($contents, "'{$id}'") || str_contains($contents, '"' . $id . '"'))) {
            require_once $file;
            return true;
        }
    }
    return false;
}

add_action('plugins_loaded', 'ap_member_dashboard_bootstrap', 20);
