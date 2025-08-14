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
            $out[$id] = [
                'id'                    => $id,
                'title'                 => $cfg['label'] ?? '',
                'roles_from_registry'   => (array)($cfg['roles'] ?? []),
                'status'                => $cfg['status'] ?? 'active',
                'callback_is_callable'  => is_callable($cb),
                'callback'              => $cb,
                'class'                 => $cfg['class'] ?? '',
            ];
        }
        return $out;
    }

    /**
     * Visibility matrix from options mapping widget id to roles.
     *
     * @return array<string,array<int,string>>
     */
    public static function get_visibility_matrix(): array
    {
        $opt = get_option('artpulse_widget_roles', []);
        return is_array($opt) ? $opt : [];
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
