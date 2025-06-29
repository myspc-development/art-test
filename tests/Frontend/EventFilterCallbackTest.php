<?php
namespace ArtPulse\Frontend\Tests;

use WP_UnitTestCase;
use WP_REST_Request;
use function ArtPulse\Frontend\ap_filter_events_callback;

class EventFilterCallbackTest extends WP_UnitTestCase
{
    private int $event1;
    private int $event2;
    private int $cat1;
    private int $cat2;

    public function set_up(): void
    {
        parent::set_up();
        add_filter('wp_die_handler', [ $this, 'get_die_handler' ]);

        $this->cat1 = wp_create_category('Music');
        $this->cat2 = wp_create_category('Art');

        $this->event1 = wp_insert_post([
            'post_title'  => 'First Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->event1, 'venue_name', 'Venue A');
        update_post_meta($this->event1, 'event_start_date', '2024-01-10');
        update_post_meta($this->event1, 'event_end_date', '2024-01-11');
        wp_set_post_terms($this->event1, [$this->cat1], 'category');

        $this->event2 = wp_insert_post([
            'post_title'  => 'Second Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);
        update_post_meta($this->event2, 'venue_name', 'Venue B');
        update_post_meta($this->event2, 'event_start_date', '2024-08-10');
        update_post_meta($this->event2, 'event_end_date', '2024-08-11');
        wp_set_post_terms($this->event2, [$this->cat2], 'category');
    }

    public function tear_down(): void
    {
        remove_filter('wp_die_handler', [ $this, 'get_die_handler' ]);
        parent::tear_down();
        $_REQUEST = [];
    }

    public function get_die_handler()
    {
        return [ $this, 'die_handler' ];
    }

    public function die_handler( $message )
    {
        // no-op to prevent exiting.
    }

    private function run_callback(array $params): string
    {
        $_REQUEST = $params;
        ob_start();
        ap_filter_events_callback();
        return ob_get_clean();
    }

    public function test_filter_by_keyword(): void
    {
        $html = $this->run_callback(['keyword' => 'First']);
        $this->assertStringContainsString('First Event', $html);
        $this->assertStringNotContainsString('Second Event', $html);
    }

    public function test_filter_by_venue(): void
    {
        $html = $this->run_callback(['venue' => 'Venue B']);
        $this->assertStringContainsString('Second Event', $html);
        $this->assertStringNotContainsString('First Event', $html);
    }

    public function test_filter_by_category_and_date(): void
    {
        $slug = get_term($this->cat2)->slug;
        $html = $this->run_callback([
            'category' => $slug,
            'after'    => '2024-08-01',
            'before'   => '2024-08-31',
        ]);
        $this->assertStringContainsString('Second Event', $html);
        $this->assertStringNotContainsString('First Event', $html);
    }
}
