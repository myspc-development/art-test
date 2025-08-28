<?php
namespace ArtPulse\Integration\Tests;

use ArtPulse\Integration\SocialAutoPoster;

class SocialAutoPosterTest extends \WP_UnitTestCase
{
    private array $requests = [];

    public function intercept($pre, $args, $url)
    {
        $this->requests[] = [$url, $args];
        return ['headers' => [], 'body' => '', 'response' => ['code' => 200]];
    }

    public function tear_down()
    {
        remove_filter('pre_http_request', [$this, 'intercept'], 10);
        parent::tear_down();
    }

    public function test_sanitize_networks_and_post_types(): void
    {
        $input = [
            'facebook' => [
                'enabled' => 'on',
                'token'   => ' fb token ',
                'page_id' => ' 123 ',
            ],
            'twitter' => [
                'enabled' => '',
                'token'   => ' tw token ' ,
            ],
            'pinterest' => [
                'token' => ' pin token ',
                'board' => ' board ' ,
            ],
            'post_types' => [
                'event' => '1',
                'artwork' => '',
                'news' => '1',
            ],
        ];

        $result = SocialAutoPoster::sanitize($input);

        $expected = [
            'facebook' => [
                'enabled' => 1,
                'token'   => sanitize_text_field(' fb token '),
                'page_id' => sanitize_text_field(' 123 '),
            ],
            'instagram' => [
                'enabled' => 0,
                'token'   => '',
            ],
            'twitter' => [
                'enabled' => 0,
                'token'   => sanitize_text_field(' tw token '),
            ],
            'pinterest' => [
                'enabled' => 0,
                'token'   => sanitize_text_field(' pin token '),
                'board'   => sanitize_text_field(' board '),
            ],
            'post_types' => [
                'event'        => 1,
                'artwork'      => 0,
                'organization' => 0,
                'artist'       => 0,
                'news'         => 1,
                'portfolio'    => 0,
            ],
        ];

        $this->assertSame($expected, $result);
    }

    public function test_register_publish_hooks_adds_actions_for_enabled_post_types(): void
    {
        update_option('ap_social_auto_post_settings', [
            'post_types' => [
                'event'     => 1,
                'artwork'   => 0,
                'organization' => 0,
                'artist'    => 0,
                'news'      => 1,
                'portfolio' => 0,
            ],
        ]);

        SocialAutoPoster::register_publish_hooks();

        $this->assertSame(10, has_action('publish_artpulse_event', [SocialAutoPoster::class, 'handle_publish']));
        $this->assertFalse(has_action('publish_artpulse_artwork', [SocialAutoPoster::class, 'handle_publish']));
        $this->assertSame(10, has_action('publish_post', [SocialAutoPoster::class, 'handle_publish']));
    }

    public function test_handle_publish_posts_to_enabled_networks(): void
    {
        update_option('ap_social_auto_post_settings', [
            'facebook' => [
                'enabled' => 1,
                'token'   => 'fb',
                'page_id' => '42',
            ],
            'instagram' => [
                'enabled' => 1,
                'token'   => 'ig',
            ],
            'twitter' => [
                'enabled' => 1,
                'token'   => 'tw',
            ],
            'pinterest' => [
                'enabled' => 1,
                'token'   => 'pin',
                'board'   => 'b123',
            ],
        ]);

        $post_id = self::factory()->post->create([
            'post_title'  => 'Auto Post',
            'post_status' => 'publish',
            'post_type'   => 'post',
        ]);
        $post    = get_post($post_id);

        add_filter('pre_http_request', [$this, 'intercept'], 10, 3);

        SocialAutoPoster::handle_publish($post_id, $post);

        $this->assertCount(4, $this->requests);

        [$fb_url, $fb_args] = $this->requests[0];
        $this->assertSame('https://graph.facebook.com/42/feed', $fb_url);
        $this->assertSame('fb', $fb_args['body']['access_token']);

        [$ig_url, $ig_args] = $this->requests[1];
        $this->assertSame('https://graph.facebook.com/me/media', $ig_url);
        $this->assertSame('ig', $ig_args['body']['access_token']);

        [$tw_url, $tw_args] = $this->requests[2];
        $this->assertSame('https://api.twitter.com/2/tweets', $tw_url);
        $this->assertSame('Bearer tw', $tw_args['headers']['Authorization']);

        [$pin_url, $pin_args] = $this->requests[3];
        $this->assertSame('https://api.pinterest.com/v5/pins', $pin_url);
        $this->assertSame('Bearer pin', $pin_args['headers']['Authorization']);
    }
}
