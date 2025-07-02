<?php
namespace ArtPulse\Frontend;

class EventListingShortcode
{
    public static function register(): void
    {
        add_shortcode('ap_event_listing', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        wp_enqueue_style(
            'ap-event-filter-form',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/css/ap-event-filter-form.css',
            [],
            '1.0.0'
        );
    }

    public static function render($atts): string
    {
        $atts = shortcode_atts([
            'posts_per_page' => 12,
            'status'         => 'publish',
            'venue'          => '',
            'after'          => '',
            'before'         => '',
            'category'       => '',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ], $atts, 'ap_event_listing');

        foreach (['venue', 'after', 'before', 'category', 'orderby'] as $field) {
            if (isset($_GET[$field])) {
                $atts[$field] = sanitize_text_field(wp_unslash($_GET[$field]));
            }
        }

        $meta_query = [];
        if (!empty($atts['venue'])) {
            $meta_query[] = [
                'key'     => 'venue_name',
                'value'   => $atts['venue'],
                'compare' => 'LIKE',
            ];
        }
        if (!empty($atts['after'])) {
            $meta_query[] = [
                'key'     => 'event_start_date',
                'value'   => $atts['after'],
                'type'    => 'DATE',
                'compare' => '>=',
            ];
        }
        if (!empty($atts['before'])) {
            $meta_query[] = [
                'key'     => 'event_end_date',
                'value'   => $atts['before'],
                'type'    => 'DATE',
                'compare' => '<=',
            ];
        }

        $tax_query = [];
        if (!empty($atts['category'])) {
            $tax_query[] = [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['category'])),
            ];
        }

        $q_args = [
            'post_type'      => 'artpulse_event',
            'post_status'    => $atts['status'],
            'posts_per_page' => intval($atts['posts_per_page']),
            'orderby'        => $atts['orderby'],
            'order'          => $atts['order'],
        ];

        if (!empty($meta_query)) {
            $q_args['meta_query'] = $meta_query;
        }
        if (!empty($tax_query)) {
            $q_args['tax_query'] = $tax_query;
        }

        if ($atts['orderby'] === 'favorite_count') {
            $q_args['orderby']  = 'meta_value_num';
            $q_args['meta_key'] = 'ap_favorite_count';
        }

        $query = new \WP_Query($q_args);

        if ($atts['orderby'] === 'rsvp_count') {
            usort($query->posts, function($a, $b) use ($atts) {
                $a_count = count((array) get_post_meta($a->ID, 'event_rsvp_list', true));
                $b_count = count((array) get_post_meta($b->ID, 'event_rsvp_list', true));
                if ($a_count === $b_count) {
                    return 0;
                }
                return $atts['order'] === 'ASC' ? $a_count <=> $b_count : $b_count <=> $a_count;
            });
        }

        ob_start();
        ?>
        <form class="ap-event-filter-form" method="get">
            <input type="text" name="venue" placeholder="Venue" value="<?php echo esc_attr($atts['venue']); ?>">
            <input type="date" name="after" value="<?php echo esc_attr($atts['after']); ?>">
            <input type="date" name="before" value="<?php echo esc_attr($atts['before']); ?>">
            <input type="text" name="category" placeholder="Category" value="<?php echo esc_attr($atts['category']); ?>">
            <button type="submit"><?php esc_html_e('Filter', 'artpulse'); ?></button>
        </form>
        <div class="ap-event-listing">
        <?php
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo ap_get_event_card(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<p>' . esc_html__('No events found.', 'artpulse') . '</p>';
        }
        ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
