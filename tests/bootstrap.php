<?php
// WordPress-less test bootstrap.

require_once __DIR__ . '/../vendor/autoload.php';

if (!defined('ARTPULSE_PLUGIN_FILE')) {
    define('ARTPULSE_PLUGIN_FILE', __FILE__);
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return preg_replace('/[^A-Za-z0-9_\-]/', '', strip_tags($str));
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($str) {
        return $str;
    }
}

