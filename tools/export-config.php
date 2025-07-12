<?php
/**
 * Utility functions for exporting and importing plugin configuration.
 */

namespace ArtPulse\Tools;

if (!defined('ABSPATH')) {
    exit;
}

function export_config(string $file = 'artpulse_export.json'): void {
    $options = get_option('artpulse_settings', []);
    file_put_contents($file, wp_json_encode($options, JSON_PRETTY_PRINT));
}

function import_config(string $file = 'artpulse_export.json'): bool {
    if (!file_exists($file)) {
        return false;
    }
    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) {
        return false;
    }
    update_option('artpulse_settings', $data);
    return true;
}
