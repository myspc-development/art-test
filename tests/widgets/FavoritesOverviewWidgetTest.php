<?php
use ArtPulse\Widgets\FavoritesOverviewWidget;

class FavoritesOverviewWidgetTest extends \WP_UnitTestCase {
    public function test_render_output() {
        $widget = new FavoritesOverviewWidget();
        $output = $widget->render();
        $this->assertNotEmpty($output, 'Render output should not be empty.');
    }
}
