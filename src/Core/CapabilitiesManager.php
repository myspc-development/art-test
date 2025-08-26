<?php

namespace ArtPulse\Core;

/**
 * Registers and assigns custom capabilities.
 */
class CapabilitiesManager
{
    public static function register(): void
    {
        add_action('init', [self::class, 'add_capabilities']);
        add_filter('map_meta_cap', [self::class, 'map_meta_cap'], 10, 4);
    }

    public static function add_capabilities(): void
    {
        $roles = ['administrator', 'editor'];

        $caps = [
            'manage_artpulse_settings',
            'edit_artpulse_content',
            'moderate_link_requests',
            'view_artpulse_dashboard',
            'view_wp_admin',
        ];

        foreach ($roles as $role_key) {
            $role = get_role($role_key);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    public static function remove_capabilities(): void
    {
        $roles = ['administrator', 'editor'];
        $caps = [
            'manage_artpulse_settings',
            'edit_artpulse_content',
            'moderate_link_requests',
            'view_artpulse_dashboard',
            'view_wp_admin',
        ];

        foreach ($roles as $role_key) {
            $role = get_role($role_key);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }

    /**
     * Map custom capabilities to meta checks.
     *
     * @param array  $caps    Primitive caps for the user.
     * @param string $cap     Capability being checked.
     * @param int    $user_id User ID.
     * @return array Modified capabilities.
     */
    public static function map_meta_cap(array $caps, string $cap, int $user_id, array $args): array
    {
        $user = get_userdata($user_id);

        // Log the capability mapping for debugging in development environments.
        if (defined('WP_DEBUG') && WP_DEBUG && get_current_user_id()) {
            $roles = $user ? implode(',', (array) $user->roles) : 'none';
            error_log(sprintf('ap map_meta_cap user=%d cap=%s roles=%s', $user_id, $cap, $roles));
        }

        if ($user && in_array('administrator', (array) $user->roles, true)) {
            // Never deny capabilities to administrators via this mapper.
            return $caps;
        }
        if ($cap === 'ap_premium_member') {
            $level = get_user_meta($user_id, 'ap_membership_level', true);
            if ($level && $level !== 'Free') {
                return ['read'];
            }
            return ['do_not_allow'];
        }
        return $caps;
    }
}
