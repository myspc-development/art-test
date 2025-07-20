<?php
if (!defined('ABSPATH')) {
    exit;
}

// Assign member role to every newly registered user
add_action('user_register', function ($user_id) {
    $user = new \WP_User($user_id);
    if (in_array('subscriber', (array) $user->roles, true)) {
        $user->set_role('member');
    }
});

// Redirect new registrants to the member dashboard
add_filter('wp_registration_redirect', function ($redirect_to) {
    return home_url('/dashboard/member');
});

// Redirect members after first login as well
add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if ($user && user_can($user, 'member')) {
        return home_url('/dashboard/member');
    }
    return $redirect_to;
}, 10, 3);
