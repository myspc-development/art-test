<?php
declare(strict_types=1);

namespace ArtPulse\Tests;

final class WidgetRolesApplyOnUpdate {
    public static function register(): void {
        $apply = static function ($value): void {
            if (!is_array($value) || !class_exists(\ArtPulse\Core\DashboardWidgetRegistry::class)) {
                return;
            }
            try {
                $ref  = new \ReflectionClass(\ArtPulse\Core\DashboardWidgetRegistry::class);
                $prop = $ref->getProperty('widgets');
                $prop->setAccessible(true);
                $widgets = $prop->getValue();
                if (!is_array($widgets)) {
                    return;
                }
                foreach ($value as $id => $conf) {
                    if (!isset($widgets[$id]) || !is_array($conf)) {
                        continue;
                    }
                    if (array_key_exists('roles', $conf)) {
                        $roles = $conf['roles'];
                        if ($roles === null) {
                            $widgets[$id]['roles'] = null;
                        } elseif (is_array($roles)) {
                            $widgets[$id]['roles'] = array_values(array_unique(array_map('strval', $roles)));
                        } else {
                            $widgets[$id]['roles'] = [ (string) $roles ];
                        }
                    }
                    if (array_key_exists('capability', $conf)) {
                        $widgets[$id]['capability'] = is_string($conf['capability']) ? $conf['capability'] : '';
                    }
                }
                $prop->setValue(null, $widgets);
            } catch (\Throwable $e) {
                // silent
            }
        };
        add_action('updated_option', static function ($option, $old, $new) use ($apply) {
            if ($option === 'artpulse_widget_roles') {
                $apply($new);
            }
        }, 10, 3);
        add_action('added_option', static function ($option, $value) use ($apply) {
            if ($option === 'artpulse_widget_roles') {
                $apply($value);
            }
        }, 10, 2);
    }
}
