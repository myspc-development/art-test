<?php
namespace ArtPulse\Tests\Stubs {

class MockStorage {
    public static array $users = [];
    public static array $user_meta = [];
    public static array $options = [];
    public static array $post_meta = [];
    public static array $json = [];
    public static bool $have_posts = true;
    public static array $removed = [];
    public static array $notice = [];
    public static array $current_roles = [];
    public static string $screen = 'dashboard';
}
}

namespace {
    use ArtPulse\Tests\Stubs\MockStorage;

    // If the WordPress test suite is available, let core functions be loaded
    // normally to avoid "cannot redeclare" errors when they are included later.
    if (defined('WP_TESTS_DIR') && file_exists(WP_TESTS_DIR . '/includes/bootstrap.php')) {
        return;
    }

    if (!function_exists('nsl_init')) {
        function nsl_init() {}
    }

    if (!function_exists('get_user_meta')) {
        function get_user_meta($uid, $key, $single = false) {
            return MockStorage::$user_meta[$uid][$key] ?? '';
        }
    }
    if (!function_exists('update_user_meta')) {
        function update_user_meta($uid, $key, $value) {
            MockStorage::$user_meta[$uid][$key] = $value;
        }
    }
    if (!function_exists('delete_user_meta')) {
        function delete_user_meta($uid, $key) {
            unset(MockStorage::$user_meta[$uid][$key]);
        }
    }
    if (!function_exists('get_option')) {
        function get_option($key, $default = false) {
            return MockStorage::$options[$key] ?? $default;
        }
    }
    if (!function_exists('update_option')) {
        function update_option($key, $value) {
            MockStorage::$options[$key] = $value;
        }
    }
    if (!function_exists('get_userdata')) {
        function get_userdata($uid) {
            return MockStorage::$users[$uid] ?? null;
        }
    }
    if (!function_exists('current_user_can')) {
        function current_user_can($cap) {
            return in_array($cap, MockStorage::$current_roles, true);
        }
    }
    if (!function_exists('wp_get_current_user')) {
        function wp_get_current_user() {
            return (object)['roles' => MockStorage::$current_roles];
        }
    }
    if (!function_exists('get_current_user_id')) {
        function get_current_user_id() {
            return 1;
        }
    }
    if (!function_exists('check_ajax_referer')) {
        function check_ajax_referer($action, $name) {}
    }
    if (!function_exists('sanitize_text_field')) {
        function sanitize_text_field($value) { return $value; }
    }
    if (!function_exists('sanitize_key')) {
        function sanitize_key($key) { return preg_replace('/[^a-z0-9_]/i', '', strtolower($key)); }
    }
    if (!function_exists('get_the_title')) {
        function get_the_title($id) { return 'Post ' . $id; }
    }
    if (!function_exists('get_permalink')) {
        function get_permalink($id) { return '/post/' . $id; }
    }
    if (!function_exists('wp_send_json')) {
        function wp_send_json($data) { MockStorage::$json = $data; }
    }
    if (!function_exists('get_header')) {
        function get_header() {}
    }
    if (!function_exists('get_footer')) {
        function get_footer() {}
    }
    if (!function_exists('have_posts')) {
        function have_posts() { return MockStorage::$have_posts; }
    }
    if (!function_exists('the_post')) {
        function the_post() { MockStorage::$have_posts = false; }
    }
    if (!function_exists('the_post_thumbnail')) {
        function the_post_thumbnail($size, $attr = []) {}
    }
    if (!function_exists('the_title')) {
        function the_title() { echo 'Test Org'; }
    }
    if (!function_exists('the_content')) {
        function the_content() { echo 'Org Content'; }
    }
    if (!function_exists('get_the_ID')) {
        function get_the_ID() { return 1; }
    }
    if (!function_exists('get_post_meta')) {
        function get_post_meta($id, $key, $single = false) {
            return MockStorage::$post_meta[$key] ?? '';
        }
    }
    if (!function_exists('esc_html')) {
        function esc_html($text) { return $text; }
    }
    if (!function_exists('esc_url')) {
        function esc_url($url) { return $url; }
    }
    if (!function_exists('remove_meta_box')) {
        function remove_meta_box($id, $screen, $context) {
            MockStorage::$removed[] = [$id, $screen, $context];
        }
    }
    if (!function_exists('wp_kses_post')) {
        function wp_kses_post($msg) { return $msg; }
    }
    if (!function_exists('set_transient')) {
        function set_transient($k, $v, $e) { MockStorage::$notice = $v; }
    }
    if (!function_exists('get_transient')) {
        function get_transient($k) { return null; }
    }
    if (!function_exists('delete_transient')) {
        function delete_transient($k) {}
    }
    if (!function_exists('esc_attr')) {
        function esc_attr($t) { return $t; }
    }
    if (!function_exists('get_current_screen')) {
        function get_current_screen() { return (object)['id' => MockStorage::$screen]; }
    }
    if (!function_exists('add_query_arg')) {
        function add_query_arg(...$args) { return '#'; }
    }
    if (!function_exists('remove_query_arg')) {
        function remove_query_arg($k) { return ''; }
    }
    if (!function_exists('wp_safe_redirect')) {
        function wp_safe_redirect($url) {}
    }
    if (!function_exists('get_users')) {
        function get_users($args = []) { return array_keys(MockStorage::$users); }
    }
    if (!function_exists('delete_user_meta')) {
        function delete_user_meta($uid, $key) { unset(MockStorage::$user_meta[$uid][$key]); }
    }
    if (!function_exists('absint')) {
        function absint($n) { return (int)$n; }
    }
}
