<?php
namespace ArtPulse\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
// Use the dashboard builder registry rather than the core registry
// so we can query widgets and render previews for the builder UI.
use ArtPulse\DashboardBuilder\DashboardWidgetRegistry;

/**
 * REST controller for the Dashboard Builder.
 */
class DashboardWidgetController {
    /**
     * Map builder widget IDs to core registry IDs.
     *
     * @var array<string,string>
     */
    private const ID_MAP = [
        'news_feed'            => 'widget_news',
        'nearby_events_map'    => 'widget_nearby_events_map',
        'my_favorites'         => 'widget_my_favorites',
        'revenue_summary'      => 'artist_revenue_summary',
        'audience_crm'         => 'audience_crm',
        'branding_settings_panel' => 'branding_settings_panel',
        'sponsored_event_config'  => 'sponsored_event_config',
        'embed_tool'              => 'embed_tool',
        'ap_donor_activity'       => 'ap_donor_activity',
        'artpulse_analytics_widget' => 'artpulse_analytics_widget',
        'org_widget_sharing'     => 'ap_widget_sharing',
        'webhooks'               => 'webhooks',
        'sponsor_display'        => 'sponsor_display',
        'rsvp_button'            => 'rsvp_button',
        'event_chat'            => 'event_chat',
        'share_this_event'      => 'share_this_event',
        'artist_inbox_preview'  => 'artist_inbox_preview',
        'artist_spotlight'      => 'artist_spotlight',
        'qa_checklist'          => 'ap_qa_checklist',
        'widget_events'         => 'widget_events',
        'widget_favorites'      => 'widget_favorites',
    ];

    /**
     * Convert a builder widget ID to the core ID.
     */
    private static function to_core_id(string $id): string {
        return \ArtPulse\Core\DashboardWidgetRegistry::map_to_core_id($id);
    }

    /**
     * Convert a core widget ID to the builder ID.
     */
    private static function to_builder_id(string $id): string {
        static $flip = null;
        if ($flip === null) {
            $flip = array_flip(self::ID_MAP);
        }
        return $flip[$id] ?? $id;
    }
    public static function register(): void {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void {
        register_rest_route('artpulse/v1', '/dashboard-widgets', [
            'methods'  => 'GET',
            'callback' => [self::class, 'get_widgets'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
        register_rest_route('artpulse/v1', '/dashboard-widgets/save', [
            'methods'  => 'POST',
            'callback' => [self::class, 'save_widgets'],
            'permission_callback' => [self::class, 'check_permissions'],
        ]);
    }

    public static function check_permissions(WP_REST_Request $request): bool {
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce) {
            $_REQUEST['X-WP-Nonce'] = $nonce;
        }
        return current_user_can('manage_options') && wp_verify_nonce($nonce, 'wp_rest');
    }

    public static function get_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $role        = sanitize_key($request->get_param('role'));
        $include_all = filter_var($request->get_param('include_all'), FILTER_VALIDATE_BOOLEAN);
        if (!$role) {
            return new WP_Error('invalid_role', __('Role parameter missing', 'artpulse'), ['status' => 400]);
        }

        $available = array_values(DashboardWidgetRegistry::get_for_role($role));
        if (empty($available) && !get_role($role)) {
            error_log('Dashboard widgets request for unsupported role: ' . $role);
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        foreach ($available as &$widget) {
            $widget['preview'] = DashboardWidgetRegistry::render($widget['id']);
        }
        unset($widget);

        $core_layout = \ArtPulse\Admin\UserLayoutManager::get_role_layout($role);
        $active_layout = [];
        foreach ($core_layout as $item) {
            $id  = is_array($item) ? ($item['id'] ?? '') : $item;
            $vis = is_array($item) ? ($item['visible'] ?? true) : true;
            $active_layout[] = [
                'id'      => self::to_builder_id($id),
                'visible' => $vis,
            ];
        }

        $response = [
            'available' => $available,
            'active'    => [
                'role'   => $role,
                'layout' => $active_layout,
            ],
        ];

        if ($include_all) {
            $response['all'] = array_values(DashboardWidgetRegistry::get_all());
        }

        return rest_ensure_response($response);
    }

    public static function save_widgets(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $data = $request->get_json_params();
        $role = sanitize_key($data['role'] ?? '');
        if (!$role) {
            return new WP_Error('invalid_role', __('Invalid role', 'artpulse'), ['status' => 400]);
        }
        if (empty(DashboardWidgetRegistry::get_for_role($role)) && !get_role($role)) {
            error_log('Attempt to save dashboard layout for unsupported role: ' . $role);
            return new WP_Error('invalid_role', __('Unsupported role', 'artpulse'), ['status' => 400]);
        }
        if (isset($data['layout']) && is_array($data['layout'])) {
            $layout = [];
            foreach ($data['layout'] as $item) {
                $id  = sanitize_key($item['id'] ?? '');
                $vis = empty($item['visible']) ? false : true;
                $layout[] = [
                    'id'      => self::to_core_id($id),
                    'visible' => $vis,
                ];
            }
            \ArtPulse\Admin\UserLayoutManager::save_role_layout($role, $layout);
        } else {
            $enabled = array_map('sanitize_key', (array) ($data['enabledWidgets'] ?? []));
            $order   = array_map('sanitize_key', (array) ($data['layoutOrder'] ?? []));
            $layout  = array_map(function ($id) { return ['id' => self::to_core_id($id), 'visible' => true]; }, $order);
            \ArtPulse\Admin\UserLayoutManager::save_role_layout($role, $layout);
        }
        return rest_ensure_response(['saved' => true]);
    }
}

