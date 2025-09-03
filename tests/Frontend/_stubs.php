<?php
namespace ArtPulse\Frontend;

/** In-memory stores */
$GLOBALS['ap_testing_current_user'] = [
    'ID'    => 111,
    'roles' => ['subscriber'],
];
$GLOBALS['ap_testing_user_meta'] = []; // [user_id => [meta_key => value]]
$GLOBALS['__ap_test_user_meta'] =& $GLOBALS['ap_testing_user_meta']; // back-compat

function ap_set_current_user_role(string $role): void {
    $GLOBALS['ap_testing_current_user']['roles'] = [$role];
    // update MockStorage if available
    if (class_exists('ArtPulse\\Tests\\Stubs\\MockStorage')) {
        \ArtPulse\Tests\Stubs\MockStorage::$users[$GLOBALS['ap_testing_current_user']['ID']] = new \WP_User($GLOBALS['ap_testing_current_user']['ID'], [$role]);
    }
}
function ap_set_user_meta(int $user_id, string $key, $value): void {
    if (!isset($GLOBALS['ap_testing_user_meta'][$user_id])) $GLOBALS['ap_testing_user_meta'][$user_id] = [];
    $GLOBALS['ap_testing_user_meta'][$user_id][$key] = $value;
    if (function_exists('update_user_meta')) {
        update_user_meta($user_id, $key, $value);
    }
}

/** Shims used by frontend rendering */
function get_current_user_id(): int { return $GLOBALS['ap_testing_current_user']['ID']; }
function is_user_logged_in(): bool { return true; }
function wp_get_current_user() {
    return (object) $GLOBALS['ap_testing_current_user'];
}
function user_can($user, string $cap): bool {
    // simplistic: admins can everything, editors edit_posts, others read
    $roles = is_object($user) ? ($user->roles ?? []) : ($GLOBALS['ap_testing_current_user']['roles'] ?? []);
    if (in_array('administrator', $roles, true)) return true;
    if ($cap === 'read') return true;
    if ($cap === 'edit_posts') return in_array('editor', $roles, true) || in_array('author', $roles, true);
    return false;
}
function current_user_can(string $cap): bool { return user_can(null, $cap); }

/** get_user_meta shim (namespaced) expected by your tests */
function get_user_meta(int $user_id, string $key, bool $single = false) {
    $store = $GLOBALS['ap_testing_user_meta'][$user_id][$key] ?? null;
    if ($single) return $store;
    return $store === null ? [] : (array) $store;
}

/** Optional: ABSPATH guard for ajax-y includes */
if (!defined('ABSPATH')) define('ABSPATH', '/tmp/');
