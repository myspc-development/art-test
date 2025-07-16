<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_Error;
use WP_REST_Response;

class UserInvitationController
{
    public static function register(): void
    {
        if (did_action('rest_api_init')) {
            self::register_routes();
        } else {
            add_action('rest_api_init', [self::class, 'register_routes']);
        }
    }

    public static function register_routes(): void
    {
        register_rest_route('artpulse/v1', '/org/(?P<id>\\d+)/invite', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'invite'],
            'permission_callback' => [self::class, 'check_permissions'],
            'args'                => [
                'id' => [
                    'validate_callback' => 'is_numeric',
                ],
            ],
        ]);

        register_rest_route('artpulse/v1', '/org/(?P<id>\\d+)/users/batch', [
            'methods'             => 'POST',
            'callback'            => [self::class, 'batch_users'],
            'permission_callback' => [self::class, 'check_permissions'],
            'args'                => [
                'id' => [
                    'validate_callback' => 'is_numeric',
                ],
            ],
        ]);
    }

    public static function check_permissions(WP_REST_Request $request): bool
    {
        $org_id  = absint($request->get_param('id'));
        if (!$org_id) {
            return false;
        }
        $user_id  = get_current_user_id();
        if (!\ArtPulse\Core\ap_user_has_org_role($user_id, $org_id, 'admin')) {
            return false;
        }
        return current_user_can('view_artpulse_dashboard');
    }

    public static function invite(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $params = $request->get_json_params();
        $emails = $params['emails'] ?? null;
        $role   = sanitize_key($params['role'] ?? 'viewer');
        $org_id  = absint($request->get_param('id'));
        $valid   = array_keys(\ArtPulse\Core\OrgRoleManager::get_roles($org_id));
        if (!in_array($role, $valid, true)) {
            $role = 'viewer';
        }
        if (!is_array($emails) || empty($emails)) {
            return new WP_Error('invalid_emails', 'Invalid emails', ['status' => 400]);
        }
        $invited = [];
        foreach ($emails as $email) {
            $email = sanitize_email($email);
            if (!$email || !is_email($email)) {
                return new WP_Error('invalid_emails', 'Invalid emails', ['status' => 400]);
            }
            \ArtPulse\Core\EmailService::send(
                $email,
                'Invitation',
                'You are invited to organization ' . $org_id
            );
            $user = get_user_by('email', $email);
            if ($user) {
                \ArtPulse\Core\MultiOrgRoles::assign_roles($user->ID, $org_id, [$role]);
            }
            $invited[] = $email;
        }
        return rest_ensure_response(['invited' => $invited, 'role' => $role]);
    }

    public static function batch_users(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $params   = $request->get_json_params();
        $action   = $params['action'] ?? '';
        $user_ids = $params['user_ids'] ?? [];
        if (!in_array($action, ['update', 'suspend', 'delete'], true)) {
            return new WP_Error('invalid_action', 'Invalid action', ['status' => 400]);
        }
        if (!is_array($user_ids) || empty($user_ids)) {
            return new WP_Error('invalid_users', 'Invalid users', ['status' => 400]);
        }
        $processed = [];
        foreach ($user_ids as $uid) {
            $uid = absint($uid);
            if (!$uid) {
                continue;
            }
            if ($action === 'update') {
                $data = $params['data'] ?? [];
                foreach ($data as $key => $value) {
                    update_user_meta($uid, sanitize_key($key), sanitize_text_field($value));
                }
                $processed[] = $uid;
            } elseif ($action === 'suspend') {
                update_user_meta($uid, 'ap_suspended', 1);
                $processed[] = $uid;
            } elseif ($action === 'delete') {
                wp_delete_user($uid);
                $processed[] = $uid;
            }
        }
        return rest_ensure_response([
            'action'    => $action,
            'processed' => $processed,
        ]);
    }
}
