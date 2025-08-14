<?php
namespace ArtPulse\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Support\WidgetIds;

require_once __DIR__ . '/../TestStubs.php';

class WidgetIdsCanonicalizationTest extends TestCase
{
    public function test_canonicalize_aliases(): void
    {
        $map = [
            'membership'               => 'widget_membership',
            'upgrade'                  => 'widget_upgrade',
            'account-tools'            => 'widget_account_tools',
            'recommended_for_you'      => 'widget_recommended_for_you',
            'my_rsvps'                 => 'widget_my_rsvps',
            'favorites'                => 'widget_favorites',
            'local-events'             => 'widget_local_events',
            'my-follows'               => 'widget_my_follows',
            'notifications'            => 'widget_notifications',
            'messages'                 => 'widget_messages',
            'dashboard_feedback'       => 'widget_dashboard_feedback',
            'cat_fact'                 => 'widget_cat_fact',
            'widget_news'              => 'widget_news_feed',
            'widget_widget_events'     => 'widget_events',
            'widget_widget_favorites'  => 'widget_favorites',
        ];
        foreach ($map as $in => $expected) {
            $this->assertSame($expected, WidgetIds::canonicalize($in));
        }
    }
}
