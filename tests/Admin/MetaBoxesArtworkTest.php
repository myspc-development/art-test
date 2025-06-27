<?php
namespace ArtPulse\Admin;

function wp_verify_nonce($nonce, $action){ return true; }
function current_user_can($cap){ return true; }
function get_post_meta($post_id, $key, $single = false){ return \ArtPulse\Admin\Tests\MetaBoxesArtworkTest::$meta[$post_id][$key] ?? ''; }
function update_post_meta($post_id, $key, $value){ \ArtPulse\Admin\Tests\MetaBoxesArtworkTest::$updated[$post_id][$key] = $value; }
function sanitize_text_field($v){ return $v; }
function sanitize_textarea_field($v){ return $v; }
function current_time($type){ return 'now'; }

namespace ArtPulse\Admin\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Admin\MetaBoxesArtwork;

class MetaBoxesArtworkTest extends TestCase
{
    public static array $meta = [];
    public static array $updated = [];

    protected function setUp(): void
    {
        self::$meta = [];
        self::$updated = [];
        $_POST = [];
    }

    public function test_price_history_recorded_when_price_changes(): void
    {
        $_POST['ead_artwork_meta_nonce_field'] = 'nonce';
        $_POST['artwork_price'] = '200';

        self::$meta[5]['artwork_price'] = '100';

        $post = (object)[ 'post_type' => 'artpulse_artwork' ];
        MetaBoxesArtwork::save_artwork_meta(5, $post);

        $this->assertArrayHasKey('price_history', self::$updated[5]);
        $history = self::$updated[5]['price_history'];
        $this->assertSame('100', $history[0]['price']);
        $this->assertSame('now', $history[0]['date']);
    }
}
