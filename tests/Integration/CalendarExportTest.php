<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Integration\CalendarExport;

/**

 * @group INTEGRATION
 */

class CalendarExportTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		CalendarExport::register();
		do_action( 'init' );
	}

	public function test_event_calendar_export_builds_ics(): void {
				$id = self::factory()->post->create(
					array(
						'post_title'   => 'Export Event',
						'post_type'    => 'artpulse_event',
						'post_status'  => 'publish',
						'post_content' => 'An event description.',
						'meta_input'   => array(
							'event_start_date' => '2030-01-01',
							'event_end_date'   => '2030-01-02',
							'venue_name'       => 'Main Hall',
						),
					)
				);

		$ref = new \ReflectionMethod( CalendarExport::class, 'build_event_ics' );
		$ref->setAccessible( true );
		$ics = $ref->invoke( null, get_post( $id ) );

		$this->assertStringContainsString( 'BEGIN:VCALENDAR', $ics );
		$this->assertStringContainsString( 'SUMMARY:Export Event', $ics );
		$this->assertStringContainsString( 'VTIMEZONE', $ics );
		$this->assertStringContainsString( 'DTSTART;TZID=', $ics );
		$this->assertStringContainsString( 'DESCRIPTION:An event description.', $ics );
		$this->assertStringContainsString( 'LOCATION:Main Hall', $ics );
		$this->assertStringContainsString( 'URL:' . get_permalink( $id ), $ics );

		$filename = sanitize_title( 'Export Event' ) . '.ics';
		$this->assertSame( 'export-event.ics', $filename );
	}

	public function test_org_calendar_export_builds_ics_for_events(): void {
				$org = self::factory()->post->create(
					array(
						'post_title'  => 'My Org',
						'post_type'   => 'artpulse_org',
						'post_status' => 'publish',
					)
				);
				$id1 = self::factory()->post->create(
					array(
						'post_title'   => 'First Event',
						'post_type'    => 'artpulse_event',
						'post_status'  => 'publish',
						'post_content' => 'First description',
						'meta_input'   => array(
							'_ap_event_organization' => $org,
							'event_start_date'       => '2031-01-01',
						),
					)
				);
				$id2 = self::factory()->post->create(
					array(
						'post_title'   => 'Second Event',
						'post_type'    => 'artpulse_event',
						'post_status'  => 'publish',
						'post_content' => 'Second description',
						'meta_input'   => array(
							'_ap_event_organization' => $org,
							'event_start_date'       => '2031-02-01',
						),
					)
				);

				$events = get_posts(
					array(
						'post_type'      => 'artpulse_event',
						'post_status'    => 'publish',
						'posts_per_page' => 2,
						'meta_key'       => '_ap_event_organization',
						'meta_value'     => $org,
					)
				);

		$ref = new \ReflectionMethod( CalendarExport::class, 'build_org_ics' );
		$ref->setAccessible( true );
		$ics = $ref->invoke( null, $events );

		$this->assertStringContainsString( 'BEGIN:VCALENDAR', $ics );
		$this->assertStringContainsString( 'SUMMARY:First Event', $ics );
		$this->assertStringContainsString( 'SUMMARY:Second Event', $ics );
		$this->assertStringContainsString( 'END:VCALENDAR', $ics );

		$filename = 'organization-' . $org . '.ics';
		$this->assertSame( 'organization-' . $org . '.ics', $filename );
	}

	public function test_artist_calendar_export_builds_ics_for_events(): void {
		$artist     = self::factory()->user->create( array( 'role' => 'author' ) );
				$id = self::factory()->post->create(
					array(
						'post_title'  => 'Artist Event',
						'post_type'   => 'artpulse_event',
						'post_status' => 'publish',
						'post_author' => $artist,
						'meta_input'  => array( 'event_start_date' => '2032-03-01' ),
					)
				);

				$events = get_posts(
					array(
						'post_type'      => 'artpulse_event',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
						'author'         => $artist,
					)
				);

		$ref = new \ReflectionMethod( CalendarExport::class, 'build_artist_ics' );
		$ref->setAccessible( true );
		$ics = $ref->invoke( null, $events );

		$this->assertStringContainsString( 'BEGIN:VCALENDAR', $ics );
		$this->assertStringContainsString( 'SUMMARY:Artist Event', $ics );
		$this->assertStringContainsString( 'END:VCALENDAR', $ics );
	}

	public function test_all_calendar_export_builds_ics_for_all_events(): void {
				$id1 = self::factory()->post->create(
					array(
						'post_title'  => 'First Global',
						'post_type'   => 'artpulse_event',
						'post_status' => 'publish',
						'meta_input'  => array( 'event_start_date' => '2033-01-01' ),
					)
				);
				$id2 = self::factory()->post->create(
					array(
						'post_title'  => 'Second Global',
						'post_type'   => 'artpulse_event',
						'post_status' => 'publish',
						'meta_input'  => array( 'event_start_date' => '2033-02-01' ),
					)
				);

				$events = get_posts(
					array(
						'post_type'      => 'artpulse_event',
						'post_status'    => 'publish',
						'posts_per_page' => 2,
					)
				);

		$ref = new \ReflectionMethod( CalendarExport::class, 'build_all_ics' );
		$ref->setAccessible( true );
		$ics = $ref->invoke( null, $events );

		$this->assertStringContainsString( 'SUMMARY:First Global', $ics );
		$this->assertStringContainsString( 'SUMMARY:Second Global', $ics );
		$this->assertStringContainsString( 'PRODID:-//ArtPulse//AllEvents//EN', $ics );
	}
}
