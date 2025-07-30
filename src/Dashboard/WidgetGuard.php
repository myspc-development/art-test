<?php
namespace ArtPulse\Dashboard;

use ArtPulse\Core\DashboardWidgetRegistry;
use ArtPulse\Widgets\Placeholder\ApPlaceholderWidget;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Guards against invalid dashboard widget callbacks and registers placeholders.
 */
class WidgetGuard
{
    /**
     * IDs of widgets patched during the current request.
     *
     * @var array<string>
     */
    private static array $patched = [];

    /**
     * Hook validation after widget registration.
     */
    public static function init(): void
    {
        add_action('init', [self::class, 'validate_and_patch'], 30);
    }

    /**
     * Validate registered widgets and patch any missing callbacks.
     */
    public static function validate_and_patch(?string $role = null): void
    {
        if (!apply_filters('ap_widget_placeholder_enabled', true)) {
            return;
        }

        if ($role !== null && method_exists(DashboardWidgetRegistry::class, 'get_role_widgets')) {
            $widgets = DashboardWidgetRegistry::get_role_widgets($role);
        } else {
            $widgets = DashboardWidgetRegistry::get_all();
        }
        $map     = apply_filters('ap_widget_placeholder_map', self::known_widget_map());

        foreach ($widgets as $id => $cfg) {
            $cb = $cfg['callback'] ?? null;
            $valid = is_callable($cb) && !self::is_default_fallback($cb);
            if ($valid) {
                continue;
            }
            error_log('[AP Widget Placeholder] Missing or invalid callback: ' . $id);
            $meta = $map[$id] ?? [
                'title'   => ucwords(str_replace('_', ' ', $id)),
                'icon'    => 'info',
                'section' => 'insights',
            ];
            self::register_placeholder($id, $meta, $cfg);
            self::$patched[] = $id;
        }
    }

    /**
     * Determine if the callback is the registry fallback.
     */
    private static function is_default_fallback($cb): bool
    {
        return is_array($cb)
            && isset($cb[0], $cb[1])
            && $cb[0] === DashboardWidgetRegistry::class
            && $cb[1] === 'render_widget_fallback';
    }

    /**
     * Overwrite a widget definition with placeholder info.
     */
    private static function register_placeholder(string $id, array $meta, array $cfg): void
    {
        $args = [ 'debug' => 'Missing callback' ];
        $callback = static function ($unused = null) use ($args) {
            ApPlaceholderWidget::render($args);
        };

        $def = $cfg;
        $def['label']       = $meta['title'] ?? ($cfg['label'] ?? $id);
        $def['icon']        = $meta['icon'] ?? ($cfg['icon'] ?? 'info');
        $def['description'] = __('Placeholder widget', 'artpulse');
        $def['callback']    = $callback;
        $def['class']       = ApPlaceholderWidget::class;
        $def['section']     = $meta['section'] ?? ($cfg['section'] ?? 'insights');

        DashboardWidgetRegistry::update_widget($id, $def);
    }

    /**
     * Default map of known widgets.
     *
     * @return array<string,array<string,string>>
     */
    public static function known_widget_map(): array
    {
        return [
            'insights'           => ['title' => __('Insights', 'artpulse'),          'icon' => 'activity', 'section' => 'insights'],
            'upcoming_events'    => ['title' => __('Upcoming Events', 'artpulse'),   'icon' => 'calendar', 'section' => 'insights'],
            'settings'           => ['title' => __('Settings', 'artpulse'),          'icon' => 'settings', 'section' => 'settings'],
            'nearby_events'      => ['title' => __('Nearby Events', 'artpulse'),     'icon' => 'map-pin',  'section' => 'insights'],
            'my_favorite_events' => ['title' => __('My Favorite Events', 'artpulse'),'icon' => 'heart',    'section' => 'insights'],
            'artist_inbox'       => ['title' => __('Artist Inbox', 'artpulse'),      'icon' => 'inbox',    'section' => 'actions'],
            'view_all_messages'  => ['title' => __('View All Messages', 'artpulse'), 'icon' => 'mail',     'section' => 'actions'],
        ];
    }

    /**
     * Return patched widget IDs.
     *
     * @return array<string>
     */
    public static function get_patched(): array
    {
        return self::$patched;
    }
}
