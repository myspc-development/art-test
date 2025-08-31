<?php
namespace ArtPulse\Tests;

use ArtPulse\Core\DashboardWidgetRegistry;

final class SeedWidgets {
    public static function widgetAlpha(): string { return '<p>alpha</p>'; }
    public static function widgetBeta(): string { return '<p>beta</p>'; }
    public static function widgetGamma(): string { return '<p>gamma</p>'; }
    public static function widgetShared(): string { return '<p>shared</p>'; }

    public static function bootstrap(): void {
        DashboardWidgetRegistry::register('widget_alpha', 'Alpha', '', '', [self::class, 'widgetAlpha'], ['roles' => ['member'], 'group' => 'insights', 'section' => 'one']);
        DashboardWidgetRegistry::register('widget_beta', 'Beta', '', '', [self::class, 'widgetBeta'], ['roles' => ['artist'], 'group' => 'insights', 'section' => 'two']);
        DashboardWidgetRegistry::register('widget_gamma', 'Gamma', '', '', [self::class, 'widgetGamma'], ['roles' => ['organization'], 'group' => 'actions', 'section' => 'one']);
        DashboardWidgetRegistry::register('widget_shared', 'Shared', '', '', [self::class, 'widgetShared'], ['roles' => ['member','artist','organization'], 'group' => 'actions', 'section' => 'two']);
        DashboardWidgetRegistry::register(
            'widget_demo',
            'Demo',
            '',
            '',
            '__return_null',
            [
                'settings' => [
                    [
                        'key'     => 'title',
                        'type'    => 'string',
                        'default' => '',
                    ],
                    [
                        'key'     => 'enabled',
                        'type'    => 'boolean',
                        'default' => false,
                    ],
                ],
            ]
        );

        add_filter('pre_option_ap_widget_group_visibility', [self::class, 'groupVisibility']);
        add_filter('pre_option_artpulse_role_layout_member', [self::class, 'memberLayout']);
        add_filter('pre_option_artpulse_role_layout_artist', [self::class, 'artistLayout']);
        add_filter('pre_option_artpulse_role_layout_organization', [self::class, 'organizationLayout']);
    }

    public static function groupVisibility(): array {
        return ['insights' => true, 'actions' => true];
    }

    public static function memberLayout(): array {
        return [ ['id' => 'widget_alpha'], ['id' => 'widget_shared'] ];
    }

    public static function artistLayout(): array {
        return [ ['id' => 'widget_beta'], ['id' => 'widget_shared'] ];
    }

    public static function organizationLayout(): array {
        return [ ['id' => 'widget_gamma'], ['id' => 'widget_shared'] ];
    }
}
