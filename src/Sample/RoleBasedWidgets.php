<?php
namespace ArtPulse\Sample;

use ArtPulse\Core\DashboardWidgetRegistry;

class RoleBasedWidgets {
    public static function register(): void {
        // ---------- MEMBER WIDGETS ----------
        DashboardWidgetRegistry::register(
            'welcome_widget',
            'Welcome Message (Role)',
            '👋',
            'Personalized welcome message for the user.',
            function () {
                $user = wp_get_current_user();
                echo '<p>Welcome, <strong>' . esc_html($user->display_name) . '</strong>!</p>';
            },
            ['category' => 'engagement', 'roles' => ['member', 'artist', 'organization']]
        );

        DashboardWidgetRegistry::register(
            'profile_progress',
            'Profile Completion',
            '✅',
            'Indicates how complete your profile is.',
            function () {
                echo '<p>Your profile is <strong>80%</strong> complete.</p>';
            },
            ['category' => 'engagement', 'roles' => ['member']]
        );

        DashboardWidgetRegistry::register(
            'upcoming_events',
            'Upcoming Events (Sample)',
            '📅',
            'List of upcoming community events.',
            function () {
                echo '<ul><li>Art Meetup – July 10</li><li>Gallery Talk – July 25</li></ul>';
            },
            ['category' => 'community', 'roles' => ['member']]
        );

        // ---------- ARTIST WIDGETS ----------
        DashboardWidgetRegistry::register(
            'portfolio_summary',
            'Portfolio Stats',
            '🖼️',
            'Shows number of artworks uploaded.',
            function () {
                $count = count_user_posts(get_current_user_id(), 'artwork');
                echo "<p>You have <strong>{$count}</strong> artworks uploaded.</p>";
            },
            ['category' => 'artist', 'roles' => ['artist']]
        );

        DashboardWidgetRegistry::register(
            'featured_artist',
            'Featured Artist',
            '🎨',
            'Displays the currently featured artist.',
            function () {
                echo '<blockquote><strong>Amira Thomas</strong> – "Color is life."</blockquote>';
            },
            ['category' => 'community', 'roles' => ['artist']]
        );

        DashboardWidgetRegistry::register(
            'artist_sales',
            'Sales Summary (Artist)',
            '💰',
            'Monthly art sales total.',
            function () {
                echo '<p>Sold <strong>3 pieces</strong> – $460 this month</p>';
            },
            ['category' => 'commerce', 'roles' => ['artist']]
        );

        // ---------- ORGANIZATION WIDGETS ----------
        DashboardWidgetRegistry::register(
            'org_team_overview',
            'Team Overview',
            '👥',
            'Shows current team members.',
            function () {
                echo '<ul><li>Alice - Curator</li><li>Bob - Admin</li></ul>';
            },
            ['category' => 'organization', 'roles' => ['organization']]
        );

        DashboardWidgetRegistry::register(
            'submission_metrics',
            'Submission Stats',
            '📤',
            'Tracks incoming artwork or application submissions.',
            function () {
                echo '<p>12 new submissions this week.</p>';
            },
            ['category' => 'reporting', 'roles' => ['organization']]
        );

        DashboardWidgetRegistry::register(
            'account_summary',
            'Billing Status',
            '💳',
            'Billing plan and renewal date.',
            function () {
                echo '<p>Plan: <strong>Premium</strong> – Renews: <strong>Aug 1</strong></p>';
            },
            ['category' => 'account', 'roles' => ['organization']]
        );

        // ---------- ADMIN WIDGETS ----------
        DashboardWidgetRegistry::register(
            'system_health',
            'Site Health Overview',
            '🛠️',
            'Core WordPress site health summary.',
            function () {
                echo '<p>All systems are operational.</p>';
            },
            ['category' => 'admin-tools', 'roles' => ['administrator']]
        );

        DashboardWidgetRegistry::register(
            'user_activity_log',
            'Recent Logins',
            '🔐',
            'List of recent user logins.',
            function () {
                echo '<ul><li>Artist123 – 1 hour ago</li><li>OrgAdmin – Today, 9:05 AM</li></ul>';
            },
            ['category' => 'admin-tools', 'roles' => ['administrator']]
        );

        DashboardWidgetRegistry::register(
            'plugin_update_status',
            'Plugin Updates',
            '🔄',
            'Overview of pending plugin updates.',
            function () {
                echo '<p>2 plugins require updates.</p>';
            },
            ['category' => 'admin-tools', 'roles' => ['administrator']]
        );
    }
}
