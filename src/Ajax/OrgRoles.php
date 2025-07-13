<?php
namespace ArtPulse\Ajax;

/**
 * AJAX endpoint: return the list of organisation roles for the current user.
 */
class OrgRoles
{
    /**
     * Hook everything up.
     */
    public static function init(): void
    {
        add_action('wp_ajax_ap_get_org_roles', [__CLASS__, 'handle']);
        add_action('wp_ajax_nopriv_ap_get_org_roles', [__CLASS__, 'handle']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue']);
    }

    /**
     * Enqueue JS and localise variables.
     */
    public static function enqueue(): void
    {
        wp_enqueue_script(
            'ap-org-roles',
            plugins_url('assets/js/ap-org-roles.js', ARTPULSE_PLUGIN_FILE),
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script(
            'ap-org-roles',
            'ArtPulseOrgRoles',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('ap_org_roles_nonce'),
                'user_id'  => get_current_user_id(),
            ]
        );
    }

    /**
     * AJAX callback.
     */
    public static function handle(): void
    {
        check_ajax_referer('ap_org_roles_nonce', 'nonce');

        $user_id = absint($_POST['user_id'] ?? 0);
        if (!$user_id) {
            wp_send_json_error('Missing user_id', 400);
        }

        // TODO: Replace with real lookup.
        $roles = [
            ['id' => 1, 'name' => 'Curator'],
            ['id' => 2, 'name' => 'Artist'],
            ['id' => 3, 'name' => 'Patron'],
        ];

        wp_send_json_success(['roles' => $roles]);
    }
}
