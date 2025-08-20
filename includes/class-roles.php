<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers custom roles and capabilities.
 */
class ArtPulse_Roles
{
    /**
     * Install roles and capabilities.
     */
    public static function install(): void
    {
        self::add_member_role();
        self::add_artist_role();
        self::add_organization_role();
    }

    private static function add_member_role(): void
    {
        $caps = [
            'read'                    => true,
            'upload_files'            => true,
            'view_artpulse_dashboard' => true,
        ];
        self::add_or_update_role('member', __('Member', 'artpulse'), $caps);
    }

    private static function add_artist_role(): void
    {
        $base = self::get_role_caps('member');
        $caps = array_merge($base, [
            'edit_artworks'    => true,
            'publish_artworks' => true,
            'delete_artworks'  => true,
        ]);
        self::add_or_update_role('artist', __('Artist', 'artpulse'), $caps);
    }

    private static function add_organization_role(): void
    {
        $base = self::get_role_caps('member');
        $caps = array_merge($base, [
            'edit_events'    => true,
            'publish_events' => true,
            'delete_events'  => true,
            'view_rsvps'     => true,
            'view_metrics'   => true,
        ]);
        self::add_or_update_role('organization', __('Organization', 'artpulse'), $caps);
    }

    /**
     * Helper to add or update a role without duplicating capabilities.
     */
    private static function add_or_update_role(string $slug, string $label, array $caps): void
    {
        if ($role = get_role($slug)) {
            foreach ($caps as $cap => $grant) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap, $grant);
                }
            }
        } else {
            add_role($slug, $label, $caps);
        }
    }

    /**
     * Retrieve capabilities for an existing role.
     *
     * @return array<string,bool>
     */
    private static function get_role_caps(string $slug): array
    {
        $role = get_role($slug);
        return $role ? $role->capabilities : [];
    }
}
