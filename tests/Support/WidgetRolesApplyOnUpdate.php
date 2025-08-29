<?php
declare(strict_types=1);

/**
 * Test-only shim:
 * Whenever tests write the 'artpulse_widget_roles' option, mirror those values
 * into DashboardWidgetRegistry::$widgets so get_all() reflects the override.
 */
if (!function_exists('ap_register_widget_roles_apply_on_update')) {
    function ap_register_widget_roles_apply_on_update(): void {

        $apply = static function ($value): void {
            if (!is_array($value)) {
                return;
            }
            if (!class_exists(\ArtPulse\Core\DashboardWidgetRegistry::class)) {
                return;
            }

            try {
                $ref  = new \ReflectionClass(\ArtPulse\Core\DashboardWidgetRegistry::class);
                $prop = $ref->getProperty('widgets');
                $prop->setAccessible(true);
                $widgets = $prop->getValue(); // static prop

                if (!is_array($widgets)) {
                    return;
                }

                foreach ($value as $id => $conf) {
                    if (!isset($widgets[$id]) || !is_array($conf)) {
                        continue;
                    }
                    if (array_key_exists('roles', $conf)) {
                        // Normalize roles: array<string>|null
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
                // Stay silent in tests
            }
        };

        // Apply on add/update of the option
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
