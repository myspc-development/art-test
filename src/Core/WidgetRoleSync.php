<?php
namespace ArtPulse\Core;

use ArtPulse\DashboardBuilder\DashboardWidgetRegistry as BuilderRegistry;

/**
 * Synchronize dashboard widget role assignments and builder registration.
 */
class WidgetRoleSync {
    /**
     * Register hooks and WP-CLI command.
     */
    public static function register(): void {
        add_action('init', [self::class, 'sync'], 30);
        if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::add_command('ap sync-widget-roles', [self::class, 'cli_sync']);
        }
    }

    /**
     * Synchronize widget roles and builder registry.
     *
     * @return array Added widgets per role.
     */
    public static function sync(): array {
        $definitions = DashboardWidgetRegistry::get_all();

        $registry    = new \ReflectionClass(DashboardWidgetRegistry::class);
        $widgetsProp = $registry->getProperty('widgets');
        $widgetsProp->setAccessible(true);
        $widgets = $widgetsProp->getValue();

        $updated = [];

        // Infer roles for widgets missing them.
        foreach ($definitions as $id => $cfg) {
            $roles = array_map('sanitize_key', (array) ($cfg['roles'] ?? []));
            if (empty($roles)) {
                $roles = self::infer_roles_from_id($id);
                $definitions[$id]['roles'] = $roles;
                if (isset($widgets[$id])) {
                    $widgets[$id]['roles'] = $roles;
                }
                $updated[$id] = $roles;
            }
        }

        $ref  = new \ReflectionClass(DashboardController::class);
        $prop = $ref->getProperty('role_widgets');
        $prop->setAccessible(true);
        $role_widgets = $prop->getValue();

        $added = [];

        // Ensure each widget is listed for all of its roles.
        foreach ($definitions as $id => $cfg) {
            $roles = array_map('sanitize_key', (array) ($cfg['roles'] ?? []));
            foreach ($roles as $role) {
                if (!isset($role_widgets[$role])) {
                    $role_widgets[$role] = [];
                }
                if (!in_array($id, $role_widgets[$role], true)) {
                    $role_widgets[$role][] = $id;
                    $added[$role][]        = $id;
                }
            }
        }

        // Update widget definitions with missing role assignments.
        foreach ($role_widgets as $role => $ids) {
            foreach ($ids as $id) {
                if (!isset($widgets[$id])) {
                    continue;
                }
                $roles = array_map('sanitize_key', (array) ($widgets[$id]['roles'] ?? []));
                if (!in_array($role, $roles, true)) {
                    $roles[] = $role;
                    $widgets[$id]['roles'] = $roles;
                }

                // Register with the Dashboard Builder if missing.
                $builder_defs = BuilderRegistry::get_all();
                if (!isset($builder_defs[$id])) {
                    BuilderRegistry::register($id, [
                        'title' => $widgets[$id]['label'] ?? ucwords(str_replace('_', ' ', $id)),
                        'render_callback' => 'render_widget_' . $id,
                        'roles' => $roles,
                        'file'  => $widgets[$id]['file'] ?? '',
                    ]);
                }
            }
        }

        // Save changes back to the registries.
        $prop->setValue($role_widgets);
        $widgetsProp->setValue($widgets);

        if ($updated && defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[WidgetRoleSync] Added roles to widgets: ' . wp_json_encode($updated));
        }

        return $added;
    }

    /**
     * WP-CLI wrapper around sync().
     */
    public static function cli_sync(): void {
        $added = self::sync();
        if (class_exists('WP_CLI')) {
            foreach ($added as $role => $ids) {
                if ($ids) {
                    \WP_CLI::log($role . ': ' . implode(', ', $ids));
                }
            }
            \WP_CLI::success('Widget roles synchronized');
        }
    }

    /**
     * Infer widget roles from its ID when not explicitly provided.
     */
    private static function infer_roles_from_id(string $id): array
    {
        $id = strtolower($id);

        if (str_contains($id, 'org') || str_contains($id, 'organization')) {
            return ['organization'];
        }

        if (str_contains($id, 'artist') || str_contains($id, 'portfolio')) {
            return ['artist'];
        }

        if (str_contains($id, 'favorite') || str_contains($id, 'events') || str_contains($id, 'news')) {
            return ['member'];
        }

        return ['member', 'artist', 'organization'];
    }
}
