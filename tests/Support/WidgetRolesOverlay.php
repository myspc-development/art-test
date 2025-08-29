<?php
declare(strict_types=1);

/**
 * Test-only overlay (FINAL):
 * Apply values from option 'artpulse_widget_roles' to final widget defs,
 * AFTER everything else has merged. Runs at very high priority.
 */
if (!function_exists('ap_register_widget_roles_overlay')) {
    function ap_register_widget_roles_overlay(): void {
        $overlay = static function ($defs) {
            if (!is_array($defs)) {
                return $defs;
            }
            $opt = get_option('artpulse_widget_roles');
            if (!is_array($opt)) {
                return $defs;
            }

            foreach ($opt as $id => $conf) {
                if (!isset($defs[$id]) || !is_array($conf)) {
                    continue;
                }

                // roles: array|string|null
                if (array_key_exists('roles', $conf)) {
                    $roles = $conf['roles'];
                    if ($roles === null) {
                        $defs[$id]['roles'] = null;
                    } elseif (is_array($roles)) {
                        $defs[$id]['roles'] = array_values(array_unique(array_map('strval', $roles)));
                    } else {
                        $defs[$id]['roles'] = [ (string) $roles ];
                    }
                }

                // capability: string
                if (array_key_exists('capability', $conf)) {
                    $defs[$id]['capability'] = is_string($conf['capability']) ? $conf['capability'] : '';
                }
            }

            return $defs;
        };

        // Run this AFTER any other definition filters/sanitizers.
        add_filter('ap_dashboard_widget_definitions',         $overlay, 1200, 1);
        add_filter('artpulse_dashboard_widget_definitions',   $overlay, 1200, 1);

        // If your code exposes a "final" stage, hook that too (harmless if absent)
        add_filter('ap_dashboard_widget_definitions_final',       $overlay, 1200, 1);
        add_filter('artpulse_dashboard_widget_definitions_final', $overlay, 1200, 1);
    }
}
