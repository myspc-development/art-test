<?php
namespace ArtPulse\Widgets\Member;

if (!defined('ABSPATH')) { exit; }

use ArtPulse\Core\DashboardWidgetInterface;
use ArtPulse\Core\DashboardWidgetRegistry;

class OrgTeamRosterWidget implements DashboardWidgetInterface {
    public static function id(): string { return 'org_team_roster'; }
    public static function label(): string { return __('Team Roster', 'artpulse'); }
    public static function roles(): array { return ['organization']; }
    public static function description(): string { return __('List and manage team members.', 'artpulse'); }

    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::id(),
            self::label(),
            'admin-users',
            self::description(),
            [self::class, 'render'],
            ['roles' => self::roles(), 'lazy' => true]
        );
    }

    public static function render(int $user_id = 0): string {
        return '<div data-widget-id="' . esc_attr(self::id()) . '">This will list team members.</div>';
    }
}

OrgTeamRosterWidget::register();
