<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ArtPulse\Core\OrgInviteManager;
use ArtPulse\Core\MultiOrgRoles;
use function ArtPulse\Core\ap_user_has_org_role;

class OrgRoleInviteController
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'routes']);
    }

    public static function routes(): void
    {
        if (!ap_rest_route_registered('artpulse/v1', '/org-roles/invite')) {
            register_rest_route('artpulse/v1', '/org-roles/invite', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'invite'],
            'permission_callback' => [self::class, 'can_invite'],
        ]);
        }

        if (!ap_rest_route_registered('artpulse/v1', '/org-roles/accept')) {
            register_rest_route('artpulse/v1', '/org-roles/accept', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'accept'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);
        }
    }

    public static function can_invite(WP_REST_Request $req): bool
    {
        $org_id = absint($req->get_param('org_id'));
        $uid    = get_current_user_id();
        return $org_id && (ap_user_has_org_role($uid, $org_id, 'admin') || user_can($uid, 'manage_options'));
    }

    public static function invite(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $email = sanitize_email($req->get_param('email'));
        $org_id = absint($req->get_param('org_id'));
        $role   = sanitize_key($req->get_param('role'));
        if (!$email || !is_email($email) || !$org_id || !$role) {
            return new WP_Error('invalid_params', 'Invalid parameters', ['status' => 400]);
        }
        $token = OrgInviteManager::create_invite($email, $org_id, $role);
        return rest_ensure_response(['token' => $token]);
    }

    public static function accept(WP_REST_Request $req): WP_REST_Response|WP_Error
    {
        $token   = sanitize_text_field($req->get_param('token'));
        $user_id = get_current_user_id();
        if (!$token) {
            return new WP_Error('invalid_token', 'Invalid token', ['status' => 400]);
        }
        $ok = OrgInviteManager::accept_invite($token, $user_id);
        if (!$ok) {
            return new WP_Error('invalid_token', 'Invalid token', ['status' => 404]);
        }
        return rest_ensure_response(['accepted' => true]);
    }
}
