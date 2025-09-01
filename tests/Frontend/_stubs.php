<?php
namespace ArtPulse\Frontend;

// Shared store so tests can control return values.
$GLOBALS['__ap_test_user_meta'] = $GLOBALS['__ap_test_user_meta'] ?? [];

/**
 * Namespaced shim so code calling unqualified get_user_meta() inside
 * ArtPulse\Frontend resolves here during tests.
 */
if (!function_exists(__NAMESPACE__ . '\\get_user_meta')) {
    function get_user_meta($user_id, $key = '', $single = false) {
        $store = $GLOBALS['__ap_test_user_meta'] ?? [];
        $val = $store[$user_id][$key] ?? null;
        return $single ? $val : (array) $val;
    }
}
