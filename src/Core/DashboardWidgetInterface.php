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
     * Render the widget output.
     *
     * Should return the rendered HTML as a string.
     */
    public static function render(): string;
}
