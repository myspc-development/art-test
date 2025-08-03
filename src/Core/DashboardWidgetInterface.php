<?php
namespace ArtPulse\Core;

interface DashboardWidgetInterface
{
    public static function id(): string;
    public static function label(): string;
    /**
     * Roles allowed to view the widget.
     *
     * @return array
     */
    public static function roles(): array;

    /**
     * Short description shown in dashboards or admin screens.
     */
    public static function description(): string;

    /**
     * Register the widget with the registry.
     */
    public static function register(): void;

    /**
     * Render the widget output for a user.
     *
     * @param int $user_id Optional user ID when rendering in a specific
     *                     context. Defaults to the current user.
     *
     * @return string Rendered HTML output.
     */
    public static function render(int $user_id = 0): string;
}
