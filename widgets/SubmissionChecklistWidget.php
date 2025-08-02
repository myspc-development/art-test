<?php
namespace ArtPulse\Widgets;

use ArtPulse\Core\DashboardWidgetRegistry;

class SubmissionChecklistWidget {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            self::get_id(),
            self::get_title(),
            'check-circle',
            __('Steps before submission', 'artpulse'),
            [self::class, 'render'],
            [
                'roles' => ['artist', 'organization'],
                'section' => self::get_section(),
            ]
        );
    }

    public static function get_id(): string { return 'submission_checklist'; }
    public static function get_title(): string { return __('Submission Checklist', 'artpulse'); }
    public static function get_section(): string { return 'actions'; }

    public static function can_view(int $user_id): bool {
        return user_can($user_id, 'artist') || user_can($user_id, 'organization');
    }

    public static function render(): string {
        $user_id = get_current_user_id();
        if (!self::can_view($user_id)) {
            return '<p class="ap-widget-no-access">' . esc_html__('You do not have access.', 'artpulse') . '</p>';
        }

        return '<p>' . esc_html__('Checklist content coming soon.', 'artpulse') . '</p>';
    }
}

SubmissionChecklistWidget::register();
