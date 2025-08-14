<?php
namespace ArtPulse\Admin\Migrations;

use ArtPulse\Core\DashboardWidgetRegistry;

class LayoutMigration
{
    public static function run(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (get_option('artpulse_layouts_migrated')) {
            return;
        }

        $default_layouts = get_option('artpulse_default_layouts');
        if (is_string($default_layouts)) {
            $decoded = json_decode($default_layouts, true);
            if (is_array($decoded)) {
                update_option('artpulse_default_layouts', $decoded);
                $default_layouts = $decoded;
            } else {
                $default_layouts = [];
            }
        } elseif (!is_array($default_layouts)) {
            $default_layouts = [];
        }

        $widget_roles = get_option('artpulse_widget_roles');
        if (is_string($widget_roles)) {
            $decoded = json_decode($widget_roles, true);
            if (is_array($decoded)) {
                update_option('artpulse_widget_roles', $decoded);
                $widget_roles = $decoded;
            } else {
                $widget_roles = [];
            }
        } elseif (!is_array($widget_roles)) {
            $widget_roles = [];
        }

        $registered = [];
        if (method_exists(DashboardWidgetRegistry::class, 'instance')) {
            $inst = DashboardWidgetRegistry::instance();
            if (is_object($inst) && method_exists($inst, 'get_registered_widgets')) {
                $registered = $inst->get_registered_widgets();
            }
        }
        if (!$registered) {
            if (method_exists(DashboardWidgetRegistry::class, 'get_definitions')) {
                $registered = DashboardWidgetRegistry::get_definitions();
            } elseif (method_exists(DashboardWidgetRegistry::class, 'get_all')) {
                $registered = DashboardWidgetRegistry::get_all();
            }
        }

        $map = [];
        foreach ($registered as $id => $_) {
            $unprefixed = preg_replace('/^widget_/','',$id);
            if ($unprefixed !== $id) {
                $map[$unprefixed] = $id;
            }
        }

        $changed_layouts = false;
        foreach ($default_layouts as $role => &$layout) {
            if (!is_array($layout)) {
                continue;
            }
            foreach ($layout as &$row) {
                if (!is_array($row)) {
                    continue;
                }
                $wid = $row['id'] ?? '';
                if (isset($registered[$wid])) {
                    continue;
                }
                if (isset($map[$wid])) {
                    $row['id'] = $map[$wid];
                    $changed_layouts = true;
                } else {
                    $un = preg_replace('/^widget_/','',$wid);
                    if (isset($map[$un])) {
                        $row['id'] = $map[$un];
                        $changed_layouts = true;
                    }
                }
            }
            unset($row);
        }
        unset($layout);
        if ($changed_layouts) {
            update_option('artpulse_default_layouts', $default_layouts);
        }

        $changed_roles = false;
        $new_roles = [];
        foreach ($widget_roles as $id => $conf) {
            if (isset($registered[$id])) {
                $new_roles[$id] = $conf;
                continue;
            }
            $target = $id;
            if (isset($map[$id])) {
                $target = $map[$id];
                $changed_roles = true;
            } else {
                $un = preg_replace('/^widget_/','',$id);
                if (isset($map[$un])) {
                    $target = $map[$un];
                    $changed_roles = true;
                }
            }
            $new_roles[$target] = $conf;
        }
        if ($changed_roles) {
            update_option('artpulse_widget_roles', $new_roles);
        }

        update_option('artpulse_layouts_migrated', 1);
    }
}

add_action('admin_init', [LayoutMigration::class, 'run']);
