<?php
namespace ArtPulse\Frontend;

class EventCardShortcode {
    public static function register(): void {
        add_shortcode('ap_event_card', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        $file = plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/event-card.css';
        if (file_exists($file)) {
            wp_enqueue_style(
                'ap-event-card',
                plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/css/event-card.css',
                [],
                filemtime($file)
            );
        }
    }

    public static function render($atts = []): string {
        $atts = shortcode_atts([
            'id' => get_the_ID(),
        ], $atts, 'ap_event_card');

        $event_id = intval($atts['id']);
        if (!$event_id) {
            return '';
        }

        $event = get_post($event_id);
        if (!$event || $event->post_type !== 'artpulse_event') {
            return '';
        }

        $meta = [
            'date'       => get_post_meta($event_id, '_ap_event_date', true),
            'venue'      => get_post_meta($event_id, '_ap_event_venue', true),
            'address'    => get_post_meta($event_id, '_ap_event_address', true),
            'time'       => get_post_meta($event_id, '_ap_event_start_time', true),
            'contact'    => get_post_meta($event_id, '_ap_event_contact', true),
            'rsvp'       => get_post_meta($event_id, '_ap_event_rsvp', true),
        ];

        $image = get_the_post_thumbnail($event_id, 'large', [
            'loading' => 'lazy',
            'alt'     => get_the_title($event_id),
        ]);

        ob_start();
        ?>
        <div class="ap-event-card">
            <div class="ap-event-card-image">
                <?php if ($image) { echo $image; } ?>
            </div>
            <div class="ap-event-card-content">
                <h3 class="ap-event-title"><?php echo esc_html(get_the_title($event_id)); ?></h3>
                <ul class="ap-event-meta">
                    <?php if ($meta['date']) : ?><li><?php echo esc_html($meta['date']); ?></li><?php endif; ?>
                    <?php if ($meta['venue']) : ?><li><?php echo esc_html($meta['venue']); ?></li><?php endif; ?>
                    <?php if ($meta['address']) : ?><li><?php echo esc_html($meta['address']); ?></li><?php endif; ?>
                    <?php if ($meta['time']) : ?><li><?php echo esc_html($meta['time']); ?></li><?php endif; ?>
                    <?php if ($meta['contact']) : ?><li><?php echo esc_html($meta['contact']); ?></li><?php endif; ?>
                    <?php if ($meta['rsvp']) : ?><li><a href="<?php echo esc_url($meta['rsvp']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('RSVP', 'artpulse'); ?></a></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <?php
        return trim(ob_get_clean());
    }
}
