<?php
use ArtPulse\Widgets\FavoritesOverviewWidget;
use ArtPulse\Community\FavoritesManager;

/**

 * @group widgets

 */

class FavoritesOverviewWidgetTest extends \WP_UnitTestCase {
	public function test_render_outputs_links() {
		FavoritesManager::install_favorites_table();
		$user_id  = self::factory()->user->create();
		$event_id = self::factory()->post->create( array( 'post_type' => 'artpulse_event' ) );
		FavoritesManager::add_favorite( $user_id, $event_id, 'artpulse_event' );

		$output = FavoritesOverviewWidget::render( $user_id );

		$this->assertStringContainsString(
			get_permalink( $event_id ),
			$output,
			'Widget should list favorited events with links.'
		);
	}

	public function test_render_shows_message_when_no_favorites() {
		FavoritesManager::install_favorites_table();
		$user_id = self::factory()->user->create();

		$output = FavoritesOverviewWidget::render( $user_id );

		$this->assertStringContainsString(
			'You have no favorite events yet.',
			$output,
			'Widget should show a helpful message when no favorites exist.'
		);
	}
}
