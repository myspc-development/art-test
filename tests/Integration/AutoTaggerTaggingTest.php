<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\AI\AutoTagger;

class AutoTaggerTaggingTest extends \WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        AutoTagger::register();
    }

    public function tear_down(): void
    {
        $_POST = [];
        parent::tear_down();
    }

    public function test_autotagger_handles_empty_post(): void
    {
        $post_id = self::factory()->post->create(['post_content' => '']);
        AutoTagger::maybe_tag($post_id, get_post($post_id), false);
        $this->assertEmpty(get_post_meta($post_id, '_suggested_tags', true));
    }

    public function test_autotagger_saves_suggested_tags_from_api(): void
    {
        $post_id = self::factory()->post->create(['post_content' => 'Art about nature']);
        add_filter('pre_http_request', static function () {
            return [
                'headers'  => [],
                'response' => ['code' => 200],
                'body'     => json_encode(['choices' => [ ['message' => ['content' => 'painting, nature']] ]]),
            ];
        });
        AutoTagger::maybe_tag($post_id, get_post($post_id), false);
        remove_all_filters('pre_http_request');
        $tags = get_post_meta($post_id, '_suggested_tags', true);
        $this->assertSame(['painting','nature'], $tags);
    }

    public function test_apply_suggested_tags_deduplicates(): void
    {
        $post_id = self::factory()->post->create(['post_content' => 'Some content']);
        update_post_meta($post_id, '_suggested_tags', ['Art','art','Design']);
        $_POST['apply_suggested_tags'] = '1';
        AutoTagger::apply_suggested_tags($post_id, get_post($post_id), true);
        $tags = wp_get_post_tags($post_id, ['fields' => 'names']);
        sort($tags);
        $this->assertSame(['Art','Design'], $tags);
    }
}
