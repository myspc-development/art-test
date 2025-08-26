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
     * Legacy slugs mapped to their canonical widget IDs.
     *
     * @var array<string,string>
     */
    private static array $aliases = [
        'membership'                   => 'widget_membership',
        'my-events'                    => 'widget_my_events',
        'account-tools'                => 'widget_account_tools',
        'widget_followed_artists'      => 'widget_my_follows',
        'followed_artists'             => 'widget_my_follows',
        'upcoming_events_by_location'  => 'widget_local_events',
        'recommended_for_you'          => 'widget_recommended_for_you',
        'site_stats'                   => 'widget_site_stats',
    ];

    /**
     * Track missing slugs already logged.
     *
     * @var array<string, bool>
     */
    private static array $logged_missing = [];

    /**
     * Override debug mode for missing widget rendering.
     */
    private static ?bool $debugOverride = null;

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
        $key = self::normalize_slug($slug);
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
        $key = self::normalize_slug($slug);
        return isset(self::$widgets[$key]);
    }

    /**
     * Render a widget by slug.
     */
    public static function render(string $slug, array $context = []): string
    {
        $key = self::normalize_slug($slug);
        if (!isset(self::$widgets[$key])) {
            if (!isset(self::$logged_missing[$key])) {
                self::$logged_missing[$key] = true;
                if (self::should_debug()) {
                    error_log('ArtPulse: Unknown widget slug: ' . $key);
                }
            }
            $escaped = function_exists('esc_attr') ? esc_attr($key) : htmlspecialchars($key, ENT_QUOTES);
            return '<section class="ap-widget--missing" data-slug="' . $escaped . '"></section>';
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

    /**
     * Retrieve all registered widget IDs.
     *
     * @return array<string>
     */
    public static function ids(): array
    {
        return array_keys(self::$widgets ?? []);
    }

    /** Return all canonical widget IDs */
    public static function get_canonical_ids(): array
    {
        return array_keys(self::$widgets);
    }

    /**
     * Override debug mode for missing widget placeholder rendering.
     */
    public static function setDebug(?bool $debug): void
    {
        self::$debugOverride = $debug;
    }

    /**
     * Clear debug override so WP_DEBUG is used instead.
     */
    public static function resetDebug(): void
    {
        self::$debugOverride = null;
    }

    /** Normalize a slug to its canonical form */
    public static function normalize_slug(string $slug): string
    {
        $s = trim(strtolower($slug));
        if (isset(self::$aliases[$s])) {
            return self::$aliases[$s];
        }
        if (strpos($s, 'widget_') !== 0) {
            $pref = 'widget_' . $s;
            if (isset(self::$widgets[$pref])) {
                return $pref;
            }
        }
        return $s;
    }

    private static function should_debug(): bool
    {
        return defined('ARTPULSE_DEBUG_VERBOSE') && ARTPULSE_DEBUG_VERBOSE
            && function_exists('is_user_logged_in') && is_user_logged_in();
    }
}

if (function_exists('add_action')) {
    add_action('init', [WidgetRegistry::class, 'init']);
}
