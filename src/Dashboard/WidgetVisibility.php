<?php
namespace ArtPulse\Dashboard;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared visibility constants for dashboard widgets.
 */
class WidgetVisibility
{
    public const PUBLIC = 'public';
    public const INTERNAL = 'internal';
    public const DEPRECATED = 'deprecated';

    /**
     * Return mapping of constant names to values.
     *
     * @return array<string,string>
     */
    public static function all(): array
    {
        return [
            'PUBLIC' => self::PUBLIC,
            'INTERNAL' => self::INTERNAL,
            'DEPRECATED' => self::DEPRECATED,
        ];
    }

    /**
     * Return list of visibility values.
     *
     * @return array<int,string>
     */
    public static function values(): array
    {
        return [
            self::PUBLIC,
            self::INTERNAL,
            self::DEPRECATED,
        ];
    }
}
