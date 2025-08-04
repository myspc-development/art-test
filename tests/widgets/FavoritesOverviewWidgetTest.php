<?php
use ArtPulse\Widgets\FavoritesOverviewWidget;
use ArtPulse\Community\FavoritesManager;

class FavoritesOverviewWidgetTest extends \WP_UnitTestCase {
    public function test_render_outputs_links() {
        FavoritesManager::install_favorites_table();
        $user_id = self::factory()->user->create();
        $event_id = self::factory()->post->create([ 'post_type' => 'artpulse_event' ]);
        FavoritesManager::add_favorite($user_id, $event_id, 'artpulse_event');

        $output = FavoritesOverviewWidget::render($user_id);

        $this->assertStringContainsString(
            get_permalink($event_id),
            $output,
            'Widget should list favorited events with links.'
        );
    }
}
