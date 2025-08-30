<?php
namespace ArtPulse\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Support\WidgetIds;

require_once __DIR__ . '/../TestStubs.php';

class WidgetIdsCanonicalizationTest extends TestCase {

        public function test_canonicalize_aliases(): void {
		$map = array(
			'membership'                  => 'widget_membership',
			'upgrade'                     => 'widget_upgrade',
                       'account-tools'               => 'widget_account_tools',
                       'widget_account-tools'        => 'widget_account_tools',
                       'my-events'                   => 'widget_my_events',
			'recommended_for_you'         => 'widget_recommended_for_you',
			'my_rsvps'                    => 'widget_my_rsvps',
			'favorites'                   => 'widget_favorites',
			'local-events'                => 'widget_local_events',
			'upcoming_events_by_location' => 'widget_local_events',
			'my-follows'                  => 'widget_my_follows',
			'widget_followed_artists'     => 'widget_my_follows',
			'followed_artists'            => 'widget_my_follows',
			'site_stats'                  => 'widget_site_stats',
			'lead_capture'                => 'widget_audience_crm',
			'rsvp_stats'                  => 'widget_org_ticket_insights',
			'webhooks'                    => 'widget_webhooks',
			'notifications'               => 'widget_notifications',
			'messages'                    => 'widget_messages',
			'dashboard_feedback'          => 'widget_dashboard_feedback',
			'cat_fact'                    => 'widget_cat_fact',
			'widget_news'                 => 'widget_news_feed',
			'widget_widget_events'        => 'widget_events',
			'widget_widget_favorites'     => 'widget_favorites',
		);
		foreach ( $map as $in => $expected ) {
			$this->assertSame( $expected, WidgetIds::canonicalize( $in ) );
                }
        }

        public function test_canonicalize_sanitization(): void {
                $map = array(
                        'A-'              => 'widget_a',
                        'B C'             => 'widget_bc',
                        'in valid/slug'   => 'widget_invalidslug',
                );
                foreach ( $map as $in => $expected ) {
                        $this->assertSame( $expected, WidgetIds::canonicalize( $in ) );
                }
        }

        public function test_canonicalize_non_strings_return_empty(): void {
               $this->assertSame( '', WidgetIds::canonicalize( array( 'widget_foo' ) ) );
                $this->assertSame( '', WidgetIds::canonicalize( null ) );
                $this->assertSame( '', WidgetIds::canonicalize( new \stdClass() ) );
	}
}
