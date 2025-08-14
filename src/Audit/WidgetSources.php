<?php
namespace ArtPulse\Audit;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Support\WidgetIds;

/**
 * Helpers to read widget configuration sources.
 */
class WidgetSources {
    /**
     * Snapshot of the widget registry.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function get_registry(): array
    {
        $defs = DashboardWidgetRegistry::get_all();
        $out  = [];
        foreach ($defs as $id => $cfg) {
            $id = WidgetIds::canonicalize($id);
            $cb = $cfg['callback'] ?? null;
            $class = $cfg['class'] ?? '';
            $out[$id] = [
                'id'                    => $id,
                'title'                 => $cfg['label'] ?? '',
                'roles_from_registry'   => (array)($cfg['roles'] ?? []),
                'status'                => $cfg['status'] ?? 'active',
                'callback_is_callable'  => is_callable($cb),
                'callback'              => $cb,
                'class'                 => $class,
                'is_placeholder'        => str_starts_with($class, 'ArtPulse\\Widgets\\Placeholder\\'),
            ];
        }
        return $out;
    }

    /**
     * Visibility matrix from options mapping widget id to roles.
     *
     * @return array<string,array<int,string>>
     */
    public static function get_visibility_roles(): array
    {
        $opt = get_option('artpulse_widget_roles', []);
        $out = [];
        if (is_array($opt)) {
            foreach ($opt as $role => $ids) {
                foreach ((array) $ids as $id) {
                    $cid = WidgetIds::canonicalize($id);
                    if (!$cid) {
                        continue;
                    }
                    $out[$cid][] = $role;
                }
            }
            foreach ($out as &$roles) {
                $roles = array_values(array_unique($roles));
            }
            unset($roles);
        }
        return $out;
    }

    /**
     * Mapping of roles to hidden widget ids.
     *
     * @return array<string,array<int,string>> role => [widget ids]
     */
    public static function get_hidden_for_roles(): array
    {
        $opt = get_option('artpulse_hidden_widgets', []);
        $out = [];
        if (is_array($opt)) {
            foreach ($opt as $role => $ids) {
                $out[$role] = array_values(array_unique(array_map([WidgetIds::class, 'canonicalize'], (array) $ids)));
            }
        }
        return $out;
    }

    /**
     * @deprecated Use get_visibility_roles() instead.
     */
    public static function get_visibility_matrix(): array
    {
        return self::get_visibility_roles();
    }

    /**
     * Return ordered list of widget ids from builder layout for a role.
     *
     * @param string $role
     * @return array<int,string>
     */
    public static function get_builder_layout(string $role): array
    {
        $layouts = get_option('artpulse_dashboard_layouts', []);
        $layout = $layouts[$role] ?? [];
        $ids = [];
        foreach ($layout as $item) {
            $id = is_array($item) ? ($item['id'] ?? '') : $item;
            $id = WidgetIds::canonicalize($id);
            if ($id && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }
}
