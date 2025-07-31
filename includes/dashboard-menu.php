<?php
/**
 * Dashboard menu configuration and helpers.
 */

if (!function_exists('ap_get_dashboard_menu_config')) {
    /**
     * Return role-based menu definitions.
     *
     * @return array<string,array<int,array<string,string>>> Menu config
     */
    function ap_get_dashboard_menu_config(): array
    {
        return [
            'member' => [
                ['id' => 'membership', 'section' => '#membership', 'label' => __('Membership', 'artpulse'), 'icon' => 'dashicons-admin-users', 'capability' => 'read'],
                ['id' => 'upgrade', 'section' => '#upgrade', 'label' => __('Upgrade', 'artpulse'), 'icon' => 'dashicons-star-filled', 'capability' => 'read'],
                ['id' => 'local-events', 'section' => '#local-events', 'label' => __('Local Events', 'artpulse'), 'icon' => 'dashicons-location-alt', 'capability' => 'read'],
                ['id' => 'favorites', 'section' => '#favorites', 'label' => __('Favorites', 'artpulse'), 'icon' => 'dashicons-heart', 'capability' => 'read'],
                ['id' => 'events', 'section' => '#events', 'label' => __('Events', 'artpulse'), 'icon' => 'dashicons-calendar', 'capability' => 'read'],
                ['id' => 'engagement', 'section' => '#engagement', 'label' => __('Activity', 'artpulse'), 'icon' => 'dashicons-format-status', 'capability' => 'read'],
                ['id' => 'account-tools', 'section' => '#account-tools', 'label' => __('Account', 'artpulse'), 'icon' => 'dashicons-download', 'capability' => 'read'],
                ['id' => 'notifications', 'section' => '#notifications', 'label' => __('Notifications', 'artpulse'), 'icon' => 'dashicons-megaphone', 'capability' => 'read'],
            ],
            'artist' => [
                ['id' => 'membership', 'section' => '#membership', 'label' => __('Membership', 'artpulse'), 'icon' => 'dashicons-admin-users', 'capability' => 'read'],
                ['id' => 'upgrade', 'section' => '#upgrade', 'label' => __('Upgrade', 'artpulse'), 'icon' => 'dashicons-star-filled', 'capability' => 'read'],
                ['id' => 'content', 'section' => '#content', 'label' => __('Content', 'artpulse'), 'icon' => 'dashicons-media-default', 'capability' => 'edit_posts'],
                ['id' => 'local-events', 'section' => '#local-events', 'label' => __('Local Events', 'artpulse'), 'icon' => 'dashicons-location-alt', 'capability' => 'read'],
                ['id' => 'favorites', 'section' => '#favorites', 'label' => __('Favorites', 'artpulse'), 'icon' => 'dashicons-heart', 'capability' => 'read'],
                ['id' => 'events', 'section' => '#events', 'label' => __('Events', 'artpulse'), 'icon' => 'dashicons-calendar', 'capability' => 'read'],
                ['id' => 'engagement', 'section' => '#engagement', 'label' => __('Activity', 'artpulse'), 'icon' => 'dashicons-format-status', 'capability' => 'read'],
                ['id' => 'account-tools', 'section' => '#account-tools', 'label' => __('Account', 'artpulse'), 'icon' => 'dashicons-download', 'capability' => 'read'],
                ['id' => 'notifications', 'section' => '#notifications', 'label' => __('Notifications', 'artpulse'), 'icon' => 'dashicons-megaphone', 'capability' => 'read'],
            ],
            'organization' => [
                ['id' => 'membership', 'section' => '#membership', 'label' => __('Membership', 'artpulse'), 'icon' => 'dashicons-admin-users', 'capability' => 'read'],
                ['id' => 'next-payment', 'section' => '#next-payment', 'label' => __('Next Payment', 'artpulse'), 'icon' => 'dashicons-money', 'capability' => 'organization'],
                ['id' => 'transactions', 'section' => '#transactions', 'label' => __('Transactions', 'artpulse'), 'icon' => 'dashicons-list-view', 'capability' => 'organization'],
                ['id' => 'upgrade', 'section' => '#upgrade', 'label' => __('Upgrade', 'artpulse'), 'icon' => 'dashicons-star-filled', 'capability' => 'read'],
                ['id' => 'content', 'section' => '#content', 'label' => __('Content', 'artpulse'), 'icon' => 'dashicons-media-default', 'capability' => 'edit_posts'],
                ['id' => 'local-events', 'section' => '#local-events', 'label' => __('Local Events', 'artpulse'), 'icon' => 'dashicons-location-alt', 'capability' => 'read'],
                ['id' => 'favorites', 'section' => '#favorites', 'label' => __('Favorites', 'artpulse'), 'icon' => 'dashicons-heart', 'capability' => 'read'],
                ['id' => 'events', 'section' => '#events', 'label' => __('Events', 'artpulse'), 'icon' => 'dashicons-calendar', 'capability' => 'read'],
                ['id' => 'engagement', 'section' => '#engagement', 'label' => __('Activity', 'artpulse'), 'icon' => 'dashicons-format-status', 'capability' => 'read'],
                ['id' => 'account-tools', 'section' => '#account-tools', 'label' => __('Account', 'artpulse'), 'icon' => 'dashicons-download', 'capability' => 'read'],
                ['id' => 'notifications', 'section' => '#notifications', 'label' => __('Notifications', 'artpulse'), 'icon' => 'dashicons-megaphone', 'capability' => 'read'],
            ],
        ];
    }
}

if (!function_exists('ap_merge_dashboard_menus')) {
    /**
     * Merge menus for multiple roles, deduplicating by ID.
     */
    function ap_merge_dashboard_menus(array $roles, bool $show_notifications = true): array
    {
        $config = ap_get_dashboard_menu_config();
        $combined = [];
        foreach ($roles as $role) {
            if (isset($config[$role])) {
                $combined = array_merge($combined, $config[$role]);
            }
        }
        $unique = [];
        foreach ($combined as $item) {
            $unique[$item['id']] = $item;
        }
        $menu = array_values($unique);
        if (!$show_notifications) {
            $menu = array_values(array_filter($menu, fn($i) => $i['id'] !== 'notifications'));
        }
        $user = is_user_logged_in() ? wp_get_current_user() : new \WP_User(0);
        /**
         * Filter dashboard sidebar links before output.
         *
         * @param array    $menu Menu items.
         * @param \WP_User $user Current user.
         */
        $menu = apply_filters('ap_dashboard_sidebar_links', $menu, $user);
        return $menu;
    }
}

// Front-end sidebar guard to hide org admin pages for members.
add_filter('ap_dashboard_sidebar_links', function (array $links, \WP_User $user): array {
    if (!user_can($user, 'view_artpulse_dashboard')) {
        $links = array_values(array_filter(
            $links,
            fn($l) => !isset($l['slug']) || !in_array($l['slug'], ['ap-org-dashboard', 'ap-org-user-manager'], true)
        ));
    }
    return $links;
}, 10, 2);
