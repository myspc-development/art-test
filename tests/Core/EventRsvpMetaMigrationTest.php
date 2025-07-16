<?php
namespace ArtPulse\Core\Tests;

use WP_UnitTestCase;
use ArtPulse\Core\EventRsvpMetaMigration;

class EventRsvpMetaMigrationTest extends WP_UnitTestCase
{
    public function test_migration_copies_and_removes_old_meta(): void
    {
        $event_id = wp_insert_post([
            'post_title'  => 'Old Event',
            'post_type'   => 'artpulse_event',
            'post_status' => 'publish',
        ]);

        update_post_meta($event_id, 'ap_event_requires_rsvp', '1');
        update_post_meta($event_id, 'ap_event_rsvps', [1, 2]);

        EventRsvpMetaMigration::maybe_migrate();

        $this->assertSame('1', get_post_meta($event_id, 'event_rsvp_enabled', true));
        $this->assertSame([1, 2], get_post_meta($event_id, 'event_rsvp_list', true));
        $this->assertEmpty(get_post_meta($event_id, 'ap_event_requires_rsvp', true));
        $this->assertEmpty(get_post_meta($event_id, 'ap_event_rsvps', true));
    }
}
