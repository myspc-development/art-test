<?php
namespace ArtPulse\Sample;

use ArtPulse\Core\DashboardWidgetRegistry;

class SampleWidgets {
    public static function register(): void {
        DashboardWidgetRegistry::register(
            'sample_welcome_widget',
            'Welcome Message',
            '👋',
            'Intro greeting for new users.',
            function () {
                echo '<p>Welcome to your dashboard! Customize your experience using the controls above.</p>';
            }
        );

        DashboardWidgetRegistry::register(
            'stats_widget',
            'Site Stats Summary',
            '📊',
            'Displays basic user and post stats.',
            function () {
                echo '<ul>
                    <li>Total Users: <strong>' . count_users()['total_users'] . '</strong></li>
                    <li>Posts Published: <strong>' . wp_count_posts()->publish . '</strong></li>
                    <li>Pages: <strong>' . wp_count_posts('page')->publish . '</strong></li>
                </ul>';
            }
        );

        DashboardWidgetRegistry::register(
            'artist_featured',
            'Featured Artist of the Week',
            '🎨',
            'Highlight an artist for promotional purposes.',
            function () {
                echo '<blockquote><strong>Amira Thomas</strong> — "Color is a power that directly influences the soul."</blockquote>';
            }
        );
    }
}

