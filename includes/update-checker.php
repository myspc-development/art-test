<?php
if (!defined('ABSPATH')) {
    exit;
}

// Load the Plugin Update Checker library.
if (!class_exists('Puc_v5p6_Factory')) {
    require_once __DIR__ . '/../vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
}

$updateChecker = Puc_v5p6_Factory::buildUpdateChecker(
    'https://github.com/your-org/artpulse-plugin',
    ARTPULSE_PLUGIN_FILE,
    'artpulse-management'
);
$updateChecker->setBranch('main');

$updateChecker->addFilter('request_info_result', function ($info, $result) {
    if ($info === null) {
        if (is_wp_error($result)) {
            error_log('Update error: ' . $result->get_error_message());
        } else {
            $body = is_array($result) ? wp_remote_retrieve_body($result) : '';
            error_log('Update error: invalid response ' . $body);
        }
    }
    return $info;
}, 10, 2);
