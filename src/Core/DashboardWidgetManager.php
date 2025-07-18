<?php
namespace ArtPulse\Core;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Admin\UserLayoutManager;
use ArtPulse\Admin\DashboardWidgetTools;

/**
 * Central manager for dashboard widgets and layouts.
 */
class DashboardWidgetManager
{
    public static function register(): void
    {
        // Placeholder for future hooks.
    }

    public static function registerWidget(
        string $id,
        string $label,
        string $icon,
        string $description,
        callable $callback,
        array $options = []
    ): void {
        DashboardWidgetRegistry::register($id, $label, $icon, $description, $callback, $options);
    }

    public static function getWidgetDefinitions(bool $include_schema = false): array
    {
        return DashboardWidgetRegistry::get_definitions($include_schema);
    }

    public static function saveUserLayout(int $user_id, array $layout): void
    {
        UserLayoutManager::save_user_layout($user_id, $layout);
    }

    public static function getUserLayout(int $user_id): array
    {
        return UserLayoutManager::get_layout_for_user($user_id);
    }

    public static function saveRoleLayout(string $role, array $layout): void
    {
        UserLayoutManager::save_role_layout($role, $layout);
    }

    public static function getRoleLayout(string $role): array
    {
        return UserLayoutManager::get_role_layout($role);
    }

    public static function exportRoleLayout(string $role): string
    {
        return UserLayoutManager::export_layout($role);
    }

    public static function importRoleLayout(string $role, string $json): bool
    {
        return UserLayoutManager::import_layout($role, $json);
    }

    public static function resetUserLayout(int $user_id): void
    {
        UserLayoutManager::reset_user_layout($user_id);
    }

    public static function renderPreview(string $role): void
    {
        DashboardWidgetTools::render_role_dashboard_preview($role);
    }
}
