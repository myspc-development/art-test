<?php
if (!defined('ABSPATH')) {
    exit;
}

$wrapper_file = __DIR__ . '/stubs.php';
if (file_exists($wrapper_file)) {
    $src = file_get_contents($wrapper_file);
    preg_match_all('/ap_widget_[a-zA-Z0-9_]+/', $src, $matches);
    $functions = array_unique($matches[0]);

    foreach ($functions as $fn) {
        if (function_exists($fn)) {
            continue;
        }
        eval(
            'function ' . $fn . '(array $vars = []): string {' .
            'if (defined("WP_DEBUG") && WP_DEBUG) {' .
            'error_log("Stub fallback invoked for ' . $fn . '");' .
            '}' .
            'return "<div class=\"ap-widget-placeholder\">Widget \"' . substr($fn, 10) . '\" is under construction.</div>";' .
            '}'
        );
    }
}
