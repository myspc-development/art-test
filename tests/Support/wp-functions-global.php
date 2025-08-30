<?php
namespace {
    if (!function_exists('wp_die')) {
        function wp_die($message = ''): void {
            throw new \RuntimeException(is_scalar($message) ? (string) $message : json_encode($message));
        }
    }
    if (!function_exists('home_url')) {
        function home_url($path = '', $scheme = null): string { // phpcs:ignore
            return '/';
        }
    }
    if (!function_exists('admin_url')) {
        function admin_url($path = '', $scheme = null): string { // phpcs:ignore
            return '/';
        }
    }
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file): string { // phpcs:ignore
            return rtrim(dirname($file), '/\\') . '/';
        }
    }
    if (!function_exists('wp_json_encode')) {
        function wp_json_encode($data, $options = 0, $depth = 512): string { // phpcs:ignore
            return json_encode($data, $options, $depth) ?: '';
        }
    }
    if (!function_exists('apply_filters')) {
        function apply_filters($tag, $value) { // phpcs:ignore
            return $value;
        }
    }
    if (!function_exists('do_action')) {
        function do_action($tag, ...$args): void { // phpcs:ignore
            // no-op
        }
    }
    if (!function_exists('wp_create_nonce')) {
        function wp_create_nonce($action = -1): string { // phpcs:ignore
            if (defined('AP_TEST_MODE') && AP_TEST_MODE) {
                return 'test-nonce';
            }
            return 'nonce';
        }
    }
    if (!function_exists('wp_verify_nonce')) {
        function wp_verify_nonce($nonce, $action = -1) { // phpcs:ignore
            if (defined('AP_TEST_MODE') && AP_TEST_MODE) {
                return $nonce === 'test-nonce';
            }
            return true;
        }
    }
    if (!function_exists('check_ajax_referer')) {
        function check_ajax_referer($action = -1, $query_arg = false, $die = true) { // phpcs:ignore
            if (defined('AP_TEST_MODE') && AP_TEST_MODE) {
                return 1;
            }
            return $die ? wp_die('bad nonce') : false;
        }
    }
}
