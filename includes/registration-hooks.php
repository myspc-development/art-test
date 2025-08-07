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

    $token = $_POST['ap_org_invite_token'] ?? $_GET['ap_org_invite_token'] ?? '';
    if ($token) {
        \ArtPulse\Core\OrgInviteManager::accept_invite(sanitize_text_field($token), $user_id);
    }
});

// Redirect new registrants to the member dashboard
add_filter('wp_registration_redirect', function ($redirect_to) {
    if (!ap_wp_admin_access_enabled()) {
        return home_url('/dashboard');
    }
    return $redirect_to;
});

// Redirect members after first login as well
add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if ($user instanceof \WP_User && user_can($user, 'member') && !ap_wp_admin_access_enabled() && !user_can($user, 'view_wp_admin')) {
        return home_url('/dashboard');
    }
    return $redirect_to;
}, 10, 3);
