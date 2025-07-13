<?php
if (!defined('ABSPATH')) {
    exit;
}

use YahnisElsts\PluginUpdateChecker\v5p6\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
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
