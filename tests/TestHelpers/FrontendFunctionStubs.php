<?php
namespace ArtPulse\Frontend;

if (!function_exists(__NAMESPACE__ . '\\is_user_logged_in')) {
    function is_user_logged_in() { return true; }
}
if (!function_exists(__NAMESPACE__ . '\\get_current_user_id')) {
    function get_current_user_id() { return 1; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) { return true; }
}
if (!function_exists(__NAMESPACE__ . '\\sanitize_text_field')) {
    function sanitize_text_field($value) { return is_string($value) ? trim($value) : $value; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_kses_post')) {
    function wp_kses_post($value) { return $value; }
}
if (!function_exists(__NAMESPACE__ . '\\sanitize_email')) {
    function sanitize_email($value) { return $value; }
}
if (!function_exists(__NAMESPACE__ . '\\check_ajax_referer')) {
    function check_ajax_referer($action, $name) {}
}
if (!function_exists(__NAMESPACE__ . '\\current_user_can')) {
    function current_user_can($cap, $id = 0) { return true; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_html')) {
    function esc_html($text) { return $text; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_url')) {
    function esc_url($url) { return $url; }
}
if (!function_exists(__NAMESPACE__ . '\\absint')) {
    function absint($n) { return (int)$n; }
}
if (!function_exists(__NAMESPACE__ . '\\esc_attr')) {
    function esc_attr($t) { return $t; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_enqueue_script')) {
    function wp_enqueue_script($handle) {}
}
if (!function_exists(__NAMESPACE__ . '\\wp_set_post_terms')) {
    function wp_set_post_terms(...$args) {}
}
if (!function_exists(__NAMESPACE__ . '\\is_wp_error')) {
    function is_wp_error($obj) { return $obj instanceof \WP_Error; }
}
if (!class_exists('WP_Error')) {
    class WP_Error {
        protected string $message;
        public function __construct($code = '', $message = '') { $this->message = $message; }
        public function get_error_message() { return $this->message; }
    }
}
if (!function_exists(__NAMESPACE__ . '\\wp_unslash')) {
    function wp_unslash($value) { return $value; }
}
if (!function_exists(__NAMESPACE__ . '\\paginate_links')) {
    function paginate_links($args) { return ''; }
}
if (!function_exists(__NAMESPACE__ . '\\add_query_arg')) {
    function add_query_arg(...$args) { return ''; }
}
if (!function_exists(__NAMESPACE__ . '\\selected')) {
    function selected($val, $cmp, $echo = true) { return $val == $cmp ? 'selected' : ''; }
}
if (!function_exists(__NAMESPACE__ . '\\sanitize_key')) {
    function sanitize_key($key) { return $key; }
}
if (!function_exists(__NAMESPACE__ . '\\get_edit_post_link')) {
    function get_edit_post_link($id) { return ''; }
}
if (!function_exists(__NAMESPACE__ . '\\wp_create_nonce')) {
    function wp_create_nonce($action) { return 'nonce'; }
}
