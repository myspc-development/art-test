<?php
/**
 * Shared Frontend function stubs for tests.
 * Centralize common WP-style helpers here so individual tests don't redeclare them.
 * All stubs are guarded with function_exists.
 */

namespace ArtPulse\Frontend {

    // ---- Auth / capability / nonce -------------------------------------------------------------

    if (!function_exists(__NAMESPACE__ . '\is_user_logged_in')) {
        function is_user_logged_in() { return true; }
    }

    if (!function_exists(__NAMESPACE__ . '\get_current_user_id')) {
        function get_current_user_id() { return 1; }
    }

    if (!function_exists(__NAMESPACE__ . '\current_user_can')) {
        function current_user_can($cap, $id = 0) { return true; }
    }

    if (!function_exists(__NAMESPACE__ . '\wp_verify_nonce')) {
        function wp_verify_nonce($nonce, $action) { return true; }
    }

    if (!function_exists(__NAMESPACE__ . '\check_ajax_referer')) {
        function check_ajax_referer($action, $name) {}
    }

    if (!function_exists(__NAMESPACE__ . '\wp_create_nonce')) {
        function wp_create_nonce($action) { return 'nonce'; }
    }

    // ---- Sanitization / escaping ---------------------------------------------------------------

    if (!function_exists(__NAMESPACE__ . '\sanitize_text_field')) {
        function sanitize_text_field($value) { return is_string($value) ? trim($value) : $value; }
    }

    if (!function_exists(__NAMESPACE__ . '\wp_kses_post')) {
        function wp_kses_post($value) { return $value; }
    }

    if (!function_exists(__NAMESPACE__ . '\sanitize_email')) {
        function sanitize_email($value) { return $value; }
    }

    if (!function_exists(__NAMESPACE__ . '\sanitize_key')) {
        function sanitize_key($key) { return $key; }
    }

    if (!function_exists(__NAMESPACE__ . '\esc_html')) {
        function esc_html($text) { return $text; }
    }

    if (!function_exists(__NAMESPACE__ . '\esc_url')) {
        function esc_url($url) { return $url; }
    }

    if (!function_exists(__NAMESPACE__ . '\esc_attr')) {
        function esc_attr($t) { return $t; }
    }

    if (!function_exists(__NAMESPACE__ . '\absint')) {
        function absint($n) { return (int) $n; }
    }

    if (!function_exists(__NAMESPACE__ . '\wp_unslash')) {
        function wp_unslash($value) { return $value; }
    }

    // ---- Options / enqueue ---------------------------------------------------------------------

    if (!function_exists(__NAMESPACE__ . '\get_option')) {
        function get_option($key, $default = false) { return $default; }
    }

    if (!function_exists(__NAMESPACE__ . '\wp_enqueue_script')) {
        function wp_enqueue_script($handle) {}
    }

    if (!function_exists(__NAMESPACE__ . '\wp_localize_script')) {
        function wp_localize_script(...$args) {}
    }

    if (!function_exists(__NAMESPACE__ . '\wp_set_post_terms')) {
        function wp_set_post_terms(...$args) {}
    }

    // ---- URL/query helpers ---------------------------------------------------------------------

    if (!function_exists(__NAMESPACE__ . '\paginate_links')) {
        function paginate_links($args) { return ''; }
    }

    if (!function_exists(__NAMESPACE__ . '\add_query_arg')) {
        function add_query_arg(...$args) { return ''; }
    }

    if (!function_exists(__NAMESPACE__ . '\selected')) {
        function selected($val, $cmp, $echo = true) { return $val == $cmp ? 'selected' : ''; }
    }

    // ---- Post helpers --------------------------------------------------------------------------

    if (!function_exists(__NAMESPACE__ . '\get_edit_post_link')) {
        function get_edit_post_link($id) { return ''; }
    }

    if (!function_exists(__NAMESPACE__ . '\get_post')) {
        function get_post($id) {
            return (object) [
                'ID'          => $id,
                'post_type'   => 'artpulse_artwork',
                'post_author' => 1,
                'post_title'  => 'Post ' . $id,
            ];
        }
    }

    if (!function_exists(__NAMESPACE__ . '\get_permalink')) {
        function get_permalink($id) { return '/event/' . $id; }
    }

    if (!function_exists(__NAMESPACE__ . '\get_the_title')) {
        function get_the_title($postOrId) {
            if (is_object($postOrId) && isset($postOrId->post_title)) {
                return $postOrId->post_title;
            }
            if (is_object($postOrId) && isset($postOrId->ID)) {
                return 'Event ' . $postOrId->ID;
            }
            return 'Event ' . $postOrId;
        }
    }

    // ---- Misc WP-style helpers -----------------------------------------------------------------

    if (!function_exists(__NAMESPACE__ . '\wpautop')) {
        function wpautop($t) { return $t; }
    }

    if (!function_exists(__NAMESPACE__ . '\sanitize_title')) {
        function sanitize_title($s) { return $s; }
    }

    if (!function_exists(__NAMESPACE__ . '\shortcode_atts')) {
        function shortcode_atts($pairs, $atts, $tag = null) { return array_merge($pairs, $atts); }
    }

    if (!function_exists(__NAMESPACE__ . '\is_wp_error')) {
        function is_wp_error($obj) { return $obj instanceof \WP_Error; }
    }

    /**
     * Shared Frontend get_user_meta stub.
     * Tests can set \ArtPulse\Frontend\Tests\FrontendState::$user_meta to control values.
     * - Prefer user-scoped value: FrontendState::$user_meta[$uid][$key]
     * - Then global value:        FrontendState::$user_meta[$key]
     * - Else:                     '' (empty string)
     */
    if (!function_exists(__NAMESPACE__ . '\get_user_meta')) {
        function get_user_meta($uid, $key, $single = false) {
            $store = \ArtPulse\Frontend\Tests\FrontendState::$user_meta;
            if (isset($store[$uid]) && array_key_exists($key, $store[$uid])) {
                return $store[$uid][$key];
            }
            return $store[$key] ?? '';
        }
    }

    /**
     * Shared Frontend get_post_meta stub.
     * Tests can set \ArtPulse\Frontend\Tests\FrontendState::$post_meta to control values.
     * - Prefer post-scoped value: FrontendState::$post_meta[$post_id][$key]
     * - Then global value:        FrontendState::$post_meta[$key]
     * - Else:                     '' (empty string)
     */
    if (!function_exists(__NAMESPACE__ . '\get_post_meta')) {
        function get_post_meta($post_id, $key, $single = false) {
            $store = \ArtPulse\Frontend\Tests\FrontendState::$post_meta;
            if (isset($store[$post_id]) && array_key_exists($key, $store[$post_id])) {
                return $store[$post_id][$key];
            }
            return $store[$key] ?? '';
        }
    }

    /* ==== Shared post meta writers ========================================== */

    if (!function_exists(__NAMESPACE__ . '\update_post_meta')) {
        function update_post_meta($post_id, $key, $value, $prev_value = '') {
            $store = \ArtPulse\Frontend\Tests\FrontendState::$post_meta;
            if (!isset($store[$post_id]) || !is_array($store[$post_id])) { $store[$post_id] = []; }
            // If $prev_value provided and current value doesn't match, do nothing (WordPress returns false)
            if ($prev_value !== '' && isset($store[$post_id][$key]) && $store[$post_id][$key] !== $prev_value) {
                return false;
            }
            $store[$post_id][$key] = $value;
            \ArtPulse\Frontend\Tests\FrontendState::$post_meta = $store;
            return true;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\add_post_meta')) {
        function add_post_meta($post_id, $key, $value, $unique = false) {
            $store = \ArtPulse\Frontend\Tests\FrontendState::$post_meta;
            if (!isset($store[$post_id]) || !is_array($store[$post_id])) { $store[$post_id] = []; }
            if ($unique && array_key_exists($key, $store[$post_id])) {
                return false;
            }
            if (isset($store[$post_id][$key])) {
                $existing = $store[$post_id][$key];
                $store[$post_id][$key] = is_array($existing)
                    ? array_merge($existing, [$value])
                    : [$existing, $value];
            } else {
                $store[$post_id][$key] = $value;
            }
            \ArtPulse\Frontend\Tests\FrontendState::$post_meta = $store;
            return true;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\delete_post_meta')) {
        function delete_post_meta($post_id, $key, $value = '') {
            $store = \ArtPulse\Frontend\Tests\FrontendState::$post_meta;
            if (!isset($store[$post_id]) || !array_key_exists($key, $store[$post_id])) {
                return false;
            }
            if ($value === '' || $store[$post_id][$key] === $value) {
                unset($store[$post_id][$key]);
                \ArtPulse\Frontend\Tests\FrontendState::$post_meta = $store;
                return true;
            }
            if (is_array($store[$post_id][$key])) {
                $store[$post_id][$key] = array_values(
                    array_filter($store[$post_id][$key], fn($v) => $v !== $value)
                );
                \ArtPulse\Frontend\Tests\FrontendState::$post_meta = $store;
                return true;
            }
            return false;
        }
    }
}

namespace ArtPulse\Frontend\Tests {
    /**
     * Shared per-test state for Frontend stubs.
     *
     * Example usage in a test's setUp():
     *   FrontendState::$user_meta = [
     *     1 => ['ap_key' => 'value'],     // user-scoped override
     *     'ap_key' => 'fallback',         // global fallback
     *   ];
     *   FrontendState::$post_meta = [
     *     42 => ['ead_org_logo_id' => 4], // post-scoped override
     *     'ap_org_theme_color' => '#abc', // global fallback
     *   ];
     */
    class FrontendState {
        public static array $user_meta = [];
        public static array $post_meta = [];
    }
}
