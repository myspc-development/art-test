<?php
namespace ArtPulse\Core;

class ShortcodeRegistry
{
    /**
     * Mapping of shortcode tags (with brackets) to their labels.
     *
     * @var array<string,string>
     */
    private static array $map = [];

    /**
     * Register a shortcode and store its label in the central registry.
     */
    public static function register(string $tag, string $label, callable $callback): void
    {
        // Store using [tag] format for consistency with legacy code.
        $key = '[' . $tag . ']';
        self::$map[$key] = function_exists('__') ? __( $label, 'artpulse' ) : $label;
        if (function_exists('add_shortcode')) {
            \add_shortcode($tag, $callback);
        }
    }

    /**
     * Retrieve all registered shortcodes.
     *
     * @return array<string,string>
     */
    public static function all(): array
    {
        return self::$map;
    }

    /**
     * Reset the registry (useful for tests).
     */
    public static function reset(): void
    {
        self::$map = [];
    }
}
