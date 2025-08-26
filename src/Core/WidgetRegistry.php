<?php
namespace ArtPulse\Core;

/**
 * Central registry for dashboard widgets.
 */
class WidgetRegistry
{
    /**
     * Registered widgets mapped by slug.
     *
     * @var array<string, array{render: callable, args: array}>
     */
    private static array $widgets = [];

    /**
     * Track missing slugs already logged.
     *
     * @var array<string, bool>
     */
    private static array $logged_missing = [];

    /**
     * Fire registration hook on init so other modules can register widgets.
     */
    public static function init(): void
    {
        do_action('artpulse/widgets/register', self::class);
    }

    /**
     * Register a widget render callback.
     */
    public static function register(string $slug, callable $render, array $args = []): void
    {
        $key = strtolower(trim($slug));
        if ($key === '') {
            return;
        }
        self::$widgets[$key] = [
            'render' => $render,
            'args'   => $args,
        ];
    }

    /**
     * Determine if a widget slug exists.
     */
    public static function exists(string $slug): bool
    {
        $key = strtolower(trim($slug));
        return isset(self::$widgets[$key]);
    }

    /**
     * Render a widget by slug.
     */
    public static function render(string $slug, array $context = []): string
    {
        $key = strtolower(trim($slug));
        if (!isset(self::$widgets[$key])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                if (!isset(self::$logged_missing[$key])) {
                    error_log('Unknown widget slug: ' . $key);
                    self::$logged_missing[$key] = true;
                }
                $escaped = function_exists('esc_attr') ? esc_attr($key) : htmlspecialchars($key, ENT_QUOTES);
                return '<section class="ap-widget--missing" data-slug="' . $escaped . '"></section>';
            }
            return '';
        }
        $def  = self::$widgets[$key];
        $args = array_merge($def['args'], $context);
        return (string) call_user_func($def['render'], $args);
    }

    /**
     * List all registered widgets.
     *
     * @return array<string>
     */
    public static function list(): array
    {
        return array_keys(self::$widgets);
    }
}

if (function_exists('add_action')) {
    add_action('init', [WidgetRegistry::class, 'init']);
}
