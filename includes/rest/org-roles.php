<?php
if (!defined('ABSPATH')) { exit; }

add_action('rest_api_init', function () {
    register_rest_route('artpulse/v1', '/org-roles', [
        'methods'             => 'GET',
        'callback'            => 'ap_get_org_roles',
        'permission_callback' => function () {
            return current_user_can('read');
        }
    ]);
});

function ap_get_org_roles() {
    return [
        ['id' => 1, 'label' => 'Curator'],
        ['id' => 2, 'label' => 'Gallery Manager'],
        ['id' => 3, 'label' => 'Event Organizer']
    ];
}
