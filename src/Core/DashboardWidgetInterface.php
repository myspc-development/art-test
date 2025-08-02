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
     * Render the widget output.
     *
     * Should return the rendered HTML as a string.
     */
    public static function render(): string;
}
