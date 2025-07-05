<?php
namespace ArtPulse\Rest\Tests;

use WP_REST_Request;
use ArtPulse\Rest\FavoriteRestController;
use ArtPulse\Community\FavoritesManager;

/**
 * @group restapi
 */
class FavoriteRestControllerTest extends \WP_UnitTestCase
{
    private int $user_id;
    private array $posts;

    public function set_up(): void
    {
        parent::set_up();
        FavoritesManager::install_favorites_table();

        $this->user_id = self::factory()->user->create();
        wp_set_current_user($this->user_id);

        $this->posts = [];
        foreach (['artpulse_event', 'artpulse_artist', 'artpulse_org', 'artpulse_artwork'] as $type) {
            $this->posts[$type] = self::factory()->post->create([
                'post_type'   => $type,
                'post_title'  => ucfirst($type),
                'post_status' => 'publish',
            ]);
        }

        FavoriteRestController::register();
        do_action('rest_api_init');
    }

    public function test_add_and_remove_favorites_for_each_type(): void
    {
        foreach ($this->posts as $type => $id) {
            // Add
            $add = new WP_REST_Request('POST', '/artpulse/v1/favorite');
            $add->set_param('object_id', $id);
            $add->set_param('object_type', $type);
            $add->set_param('action', 'add');
            $res = rest_get_server()->dispatch($add);
            $this->assertSame(200, $res->get_status());
            $this->assertSame([
                'success' => true,
                'status'  => 'added',
            ], $res->get_data());
            $this->assertTrue(FavoritesManager::is_favorited($this->user_id, $id, $type));
            $this->assertSame('1', get_post_meta($id, 'ap_favorite_count', true));

            // Remove
            $remove = new WP_REST_Request('POST', '/artpulse/v1/favorite');
            $remove->set_param('object_id', $id);
            $remove->set_param('object_type', $type);
            $remove->set_param('action', 'remove');
            $res = rest_get_server()->dispatch($remove);
            $this->assertSame(200, $res->get_status());
            $this->assertSame([
                'success' => true,
                'status'  => 'removed',
            ], $res->get_data());
            $this->assertFalse(FavoritesManager::is_favorited($this->user_id, $id, $type));
            $this->assertSame('0', get_post_meta($id, 'ap_favorite_count', true));
        }
    }
}
