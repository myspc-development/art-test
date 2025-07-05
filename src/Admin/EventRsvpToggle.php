<?php
namespace ArtPulse\Admin;

class EventRsvpToggle
{
    public static function register(): void
    {
        add_action('add_meta_boxes_artpulse_event', [self::class, 'add_meta_box']);
        add_action('save_post_artpulse_event', [self::class, 'save_meta']);
    }

    public static function add_meta_box(\WP_Post $post): void
    {
        add_meta_box(
            'ap_event_rsvp_toggle',
            __('RSVP Settings', 'artpulse'),
            [self::class, 'render'],
            'artpulse_event',
            'side'
        );
    }

    public static function render(\WP_Post $post): void
    {
        $enabled = get_post_meta($post->ID, 'ap_event_requires_rsvp', true);
        echo '<label><input type="checkbox" name="ap_event_requires_rsvp" value="1" ' . checked($enabled, '1', false) . '> ' . esc_html__('Requires RSVP', 'artpulse') . '</label>';
    }

    public static function save_meta(int $post_id): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        update_post_meta($post_id, 'ap_event_requires_rsvp', isset($_POST['ap_event_requires_rsvp']) ? '1' : '');
    }
}
