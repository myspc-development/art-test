<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Integration\PortfolioSync;

/**

 * @group integration

 */

class PortfolioSyncTest extends \WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Ensure post types and hooks are registered
		do_action( 'init' );
	}

	public function test_portfolio_meta_set_on_insert(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$event_id = wp_insert_post(
			array(
				'post_title'  => 'Sync Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $user_id,
				'meta_input'  => array(
					'event_city' => 'TestCity',
				),
			)
		);

		$portfolio = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $event_id,
				'post_status' => 'any',
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);

		$this->assertCount( 1, $portfolio );
		$portfolio_id = $portfolio[0];
		$this->assertSame(
			$event_id,
			(int) get_post_meta( $portfolio_id, '_ap_source_post', true )
		);
		$this->assertSame( 'artpulse_event', get_post_meta( $portfolio_id, '_ap_source_type', true ) );
		$terms = wp_get_object_terms( $portfolio_id, 'project-category', array( 'fields' => 'names' ) );
		$this->assertContains( 'Event', $terms );
	}

	public function test_event_meta_copied_to_portfolio(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$event_id = wp_insert_post(
			array(
				'post_title'  => 'Meta Sync Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $user_id,
			)
		);

		update_post_meta( $event_id, '_ap_event_date', '2030-01-01' );
		update_post_meta( $event_id, '_ap_event_venue', 'The Venue' );
		update_post_meta( $event_id, '_ap_event_start_time', '20:00' );

		wp_update_post( array( 'ID' => $event_id ) );

		$portfolio = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $event_id,
				'post_status' => 'any',
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);

		$this->assertCount( 1, $portfolio );
		$portfolio_id = $portfolio[0];
		$this->assertSame( '2030-01-01', get_post_meta( $portfolio_id, '_ap_event_date', true ) );
		$this->assertSame( 'The Venue', get_post_meta( $portfolio_id, '_ap_event_venue', true ) );
		$this->assertSame( '20:00', get_post_meta( $portfolio_id, '_ap_event_start_time', true ) );
	}

	public function test_gallery_meta_copied_to_portfolio(): void {
		$user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );

		$event_id = wp_insert_post(
			array(
				'post_title'  => 'Gallery Event',
				'post_type'   => 'artpulse_event',
				'post_status' => 'publish',
				'post_author' => $user_id,
				'meta_input'  => array(
					'_ap_submission_images' => array( 11, 22 ),
				),
			)
		);

		$portfolio = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $event_id,
				'post_status' => 'any',
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);

		$this->assertCount( 1, $portfolio );
		$portfolio_id = $portfolio[0];

		$this->assertSame(
			array(
				11,
				22,
			),
			get_post_meta( $portfolio_id, '_ap_submission_images', true )
		);
		$this->assertSame( 11, (int) get_post_thumbnail_id( $portfolio_id ) );
	}

	public function test_org_meta_synced_and_deleted(): void {
		$user = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user );

		$org_id = wp_insert_post(
			array(
				'post_title'  => 'Sync Org',
				'post_type'   => 'artpulse_org',
				'post_status' => 'publish',
				'post_author' => $user,
				'meta_input'  => array(
					'_ap_submission_images'  => array( 55 ),
					'ead_org_logo_id'        => 101,
					'ead_org_banner_id'      => 202,
					'ead_org_website_url'    => 'https://example.com',
					'ead_org_street_address' => '123 Main St',
					'ead_org_type'           => 'gallery',
				),
			)
		);

		$portfolio = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $org_id,
				'post_status' => 'any',
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);

		$this->assertCount( 1, $portfolio );
		$portfolio_id = $portfolio[0];
		$this->assertSame( 55, (int) get_post_thumbnail_id( $portfolio_id ) );
		$this->assertSame( array( 55 ), get_post_meta( $portfolio_id, '_ap_submission_images', true ) );
		$this->assertSame( '101', get_post_meta( $portfolio_id, 'ead_org_logo_id', true ) );
		$this->assertSame( '202', get_post_meta( $portfolio_id, 'ead_org_banner_id', true ) );
		$this->assertSame( 'https://example.com', get_post_meta( $portfolio_id, 'ead_org_website_url', true ) );
		$this->assertSame( '123 Main St', get_post_meta( $portfolio_id, 'ead_org_street_address', true ) );
		$this->assertSame( 'gallery', get_post_meta( $portfolio_id, 'ead_org_type', true ) );

		// Delete org, portfolio should also be removed
		wp_delete_post( $org_id, true );
		$remaining = get_posts(
			array(
				'post_type'   => 'portfolio',
				'meta_key'    => '_ap_source_post',
				'meta_value'  => $org_id,
				'post_status' => 'any',
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);

		$this->assertEmpty( $remaining );
	}
}
