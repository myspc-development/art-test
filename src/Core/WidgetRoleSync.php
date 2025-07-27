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
        $registry = new \ReflectionClass(DashboardWidgetRegistry::class);
        $widgetsProp = $registry->getProperty('widgets');
        $widgetsProp->setAccessible(true);
        $widgets = $widgetsProp->getValue();

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
                        'render_callback' => static function () use ($id) {
                            echo '<div class="ap-widget-placeholder">' . esc_html($id) . '</div>';
                        },
                        'roles' => $roles,
                        'file'  => $widgets[$id]['file'] ?? '',
                    ]);
                }
            }
        }

        // Save changes back to the registries.
        $prop->setValue($role_widgets);
        $widgetsProp->setValue($widgets);

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
}
