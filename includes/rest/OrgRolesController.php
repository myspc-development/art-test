<?php

namespace ArtPulse\Rest;

use WP_REST_Controller;

class OrgRolesController extends WP_REST_Controller {
    public function __construct() {
        $this->namespace = 'artpulse/v1';
        $this->rest_base = 'roles';
    }

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_roles'],
                'permission_callback' => [$this, 'can_manage_roles'],
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_role'],
                'permission_callback' => [$this, 'can_manage_roles'],
            ],
        ]);
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<role_key>[a-zA-Z0-9_-]+)', [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_role'],
                'permission_callback' => [$this, 'can_manage_roles'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete_role'],
                'permission_callback' => [$this, 'can_manage_roles'],
            ],
        ]);
    }

    // Permission check
    public function can_manage_roles() {
        return current_user_can('manage_options'); // Or your custom cap
    }

    // Retrieve all roles
    public function get_roles($request) {
        global $wpdb;
        $table = $wpdb->prefix . 'ap_roles';

        $rows = $wpdb->get_results("SELECT role_key, display_name, parent_role_key FROM $table", ARRAY_A);
        if (!is_array($rows)) {
            $rows = [];
        }

        return rest_ensure_response($rows);
    }

    // Create a new role
    public function create_role($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $role_key = sanitize_key($params['role_key'] ?? '');
        $label    = sanitize_text_field($params['label'] ?? '');
        $parent   = isset($params['parent_role_key']) ? sanitize_key($params['parent_role_key']) : null;

        if (!$role_key || !$label) {
            return new \WP_Error('invalid_data', 'Invalid role data', ['status' => 400]);
        }

        $table = $wpdb->prefix . 'ap_roles';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT role_key FROM $table WHERE role_key = %s", $role_key));
        if ($exists) {
            return new \WP_Error('role_exists', 'Role already exists', ['status' => 409]);
        }

        $wpdb->insert($table, [
            'role_key'        => $role_key,
            'parent_role_key' => $parent,
            'display_name'    => $label,
        ]);

        return rest_ensure_response(['success' => true]);
    }

    // Update a role
    public function update_role($request) {
        global $wpdb;
        $role_key = sanitize_key($request['role_key']);
        $params   = $request->get_json_params();

        $data = [];
        if (isset($params['label'])) {
            $data['display_name'] = sanitize_text_field($params['label']);
        }
        if (array_key_exists('parent_role_key', $params)) {
            $parent = $params['parent_role_key'];
            $data['parent_role_key'] = $parent !== null ? sanitize_key($parent) : null;
        }

        if (empty($data)) {
            return new \WP_Error('invalid_data', 'Nothing to update', ['status' => 400]);
        }

        $table = $wpdb->prefix . 'ap_roles';
        $wpdb->update($table, $data, ['role_key' => $role_key]);

        return rest_ensure_response(['success' => true]);
    }

    // Delete a role
    public function delete_role($request) {
        global $wpdb;
        $role_key = sanitize_key($request['role_key']);

        // Prevent deleting core roles
        $protected = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
        if (in_array($role_key, $protected, true)) {
            return new \WP_Error('protected_role', 'Cannot delete protected role', ['status' => 400]);
        }

        // Ensure role not assigned to any user
        $rel_table = $wpdb->prefix . 'ap_org_user_roles';
        $in_use = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $rel_table WHERE role = %s", $role_key));
        if ($in_use > 0) {
            return new \WP_Error('role_in_use', 'Role is assigned to users', ['status' => 400]);
        }

        $table = $wpdb->prefix . 'ap_roles';
        $wpdb->delete($table, ['role_key' => $role_key]);

        return rest_ensure_response(['success' => true]);
    }
}
