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

    // Example methods to implement
    public function get_roles($request) {/* ... */}
    public function create_role($request) {/* ... */}
    public function update_role($request) {/* ... */}
    public function delete_role($request) {/* ... */}
}
