<?php
namespace ArtPulse\Audit;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;

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
        return is_array($opt) ? $opt : [];
    }

    /**
     * Mapping of roles to hidden widget ids.
     *
     * @return array<string,array<int,string>> role => [widget ids]
     */
    public static function get_hidden_for_roles(): array
    {
        $opt = get_option('artpulse_hidden_widgets', []);
        return is_array($opt) ? $opt : [];
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
        $res = UserLayoutManager::get_role_layout($role);
        $layout = $res['layout'] ?? [];
        $ids = [];
        foreach ($layout as $item) {
            $ids[] = is_array($item) ? ($item['id'] ?? '') : $item;
        }
        return array_values(array_filter($ids));
    }
}
