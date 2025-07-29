<?php
namespace ArtPulse\Frontend;

class EventListShortcode {
    public static function register(): void {
        add_shortcode('ap_event_list', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
        $plugin_url = plugin_dir_url(ARTPULSE_PLUGIN_FILE);
        if (file_exists(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/event-card.css')) {
            wp_enqueue_style(
                'ap-event-card',
                $plugin_url . 'assets/css/event-card.css',
                [],
                filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/event-card.css')
            );
        }
        if (file_exists(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/event-listing.css')) {
            wp_enqueue_style(
                'ap-event-list',
                $plugin_url . 'assets/css/event-listing.css',
                ['ap-event-card'],
                filemtime(plugin_dir_path(ARTPULSE_PLUGIN_FILE) . 'assets/css/event-listing.css')
            );
        }
    }

    public static function render($atts = []): string {
        $atts = shortcode_atts([
            'posts_per_page' => 6,
            'after'          => '',
            'before'         => '',
            'category'       => '',
            'event_type'     => '',
            'sort'           => 'soonest',
            'layout'         => 'grid',
        ], $atts, 'ap_event_list');

        $meta_query = [];
        if ($atts['after'] !== '') {
            $meta_query[] = [
                'key'     => 'event_start_date',
                'value'   => sanitize_text_field($atts['after']),
                'compare' => '>=',
                'type'    => 'DATE',
            ];
        }
        if ($atts['before'] !== '') {
            $meta_query[] = [
                'key'     => 'event_end_date',
                'value'   => sanitize_text_field($atts['before']),
                'compare' => '<=',
                'type'    => 'DATE',
            ];
        }

        $tax_query = [];
        if ($atts['category'] !== '') {
            $tax_query[] = [
                'taxonomy' => 'category',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', sanitize_text_field($atts['category']))),
            ];
        }
        if ($atts['event_type'] !== '') {
            $tax_query[] = [
                'taxonomy' => 'event_type',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', sanitize_text_field($atts['event_type']))),
            ];
        }

        $orderby  = 'meta_value';
        $order    = 'ASC';
        $meta_key = 'event_start_date';
        if ($atts['sort'] === 'az') {
            $orderby  = 'title';
            $meta_key = '';
        } elseif ($atts['sort'] === 'newest') {
            $orderby  = 'date';
            $order    = 'DESC';
            $meta_key = '';
        }

        $query_args = [
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'posts_per_page' => intval($atts['posts_per_page']),
            'orderby'        => $orderby,
            'order'          => $order,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ];
        if ($meta_key) {
            $query_args['meta_key'] = $meta_key;
        }
        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }
        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query($query_args);
        if (empty($query->posts)) {
            return '<p>' . esc_html__('No events found.', 'artpulse') . '</p>';
        }

        $layout = $atts['layout'] === 'list' ? 'list' : 'grid';
        ob_start();
        echo '<div class="ap-event-list ap-event-list-' . esc_attr($layout) . '">';
        foreach ($query->posts as $eid) {
            echo ap_get_event_card($eid);
        }
        echo '</div>';
        return ob_get_clean();
    }
}
