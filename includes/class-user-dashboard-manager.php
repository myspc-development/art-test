<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Manages dashboard widgets and layouts for users.
 */
class UserDashboardManager {
    /** @var array<string,array> */
    private static $registry = [];
    private const META_KEY = '_user_dashboard_layout';

    /**
     * Register a dashboard widget configuration.
     *
     * @param string $id     Widget identifier.
     * @param array  $config Widget configuration with callback, roles, and title.
     * @return bool Whether the widget was registered.
     */
    public static function register_widget($id, $config) {
        if (!is_string($id) || $id === '' || !is_array($config)) {
            return false;
        }

        $defaults = [
            'id'       => $id,
            'callback' => null,
            'roles'    => [],
            'title'    => '',
        ];
        $config = array_merge($defaults, $config);

        if (!is_callable($config['callback'])) {
            return false;
        }
        if (!is_array($config['roles'])) {
            $config['roles'] = [];
        }

        self::$registry[$id] = $config;
        return true;
    }

    /**
     * Retrieve widgets permitted for a user based on role.
     *
     * @param int $user_id User ID.
     * @return array<string,array> Widgets available to the user keyed by id.
     */
    public static function get_widgets_for_user($user_id) {
        $user = get_userdata($user_id);
        if (!$user || !is_array($user->roles)) {
            return [];
        }
        $user_roles = $user->roles;
        $widgets    = [];

        foreach (self::$registry as $id => $widget) {
            $roles = isset($widget['roles']) && is_array($widget['roles']) ? $widget['roles'] : [];
            if (empty($roles) || array_intersect($roles, $user_roles)) {
                $widgets[$id] = $widget;
            }
        }

        return $widgets;
    }

    /**
     * Render the dashboard for a user.
     *
     * @param int $user_id User ID.
     * @return void
     */
    public static function render_dashboard($user_id) {
        $layout  = self::load_user_layout($user_id);
        $widgets = self::get_widgets_for_user($user_id);

        if (!is_array($layout) || empty($layout)) {
            $layout = array_keys($widgets);
        }

        foreach ($layout as $widget_id) {
            if (isset($widgets[$widget_id]) && is_callable($widgets[$widget_id]['callback'])) {
                call_user_func($widgets[$widget_id]['callback'], $user_id, $widgets[$widget_id]);
            }
        }
    }

    /**
     * Save a user's dashboard layout.
     *
     * @param int   $user_id User ID.
     * @param array $layout  Array of widget IDs representing layout order.
     * @return bool Whether the layout was saved.
     */
    public static function save_user_layout($user_id, $layout) {
        if (!is_array($layout)) {
            return false;
        }

        return update_user_meta($user_id, self::META_KEY, $layout);
    }

    /**
     * Load a user's dashboard layout.
     *
     * @param int $user_id User ID.
     * @return array Layout of widget IDs.
     */
    public static function load_user_layout($user_id) {
        $layout = get_user_meta($user_id, self::META_KEY, true);
        return is_array($layout) ? $layout : [];
    }

    /**
     * Reset a user's dashboard layout.
     *
     * @param int $user_id User ID.
     * @return bool Whether the layout was cleared.
     */
    public static function reset_user_layout($user_id) {
        if (!$user_id) {
            return false;
        }

        return delete_user_meta($user_id, self::META_KEY);
    }
}
