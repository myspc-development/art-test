<?php
namespace ArtPulse\Frontend;

/**
 * Frontend event filter form and AJAX callback.
 */
class EventFilter
{
    public static function register(): void
    {
        add_shortcode('ap_event_filter', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_action('wp_ajax_ap_filter_events', __NAMESPACE__ . '\\ap_filter_events_callback');
        add_action('wp_ajax_nopriv_ap_filter_events', __NAMESPACE__ . '\\ap_filter_events_callback');
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
        wp_enqueue_script(
            'ap-event-filter',
            plugin_dir_url(ARTPULSE_PLUGIN_FILE) . 'assets/js/ap-event-filter.js',
            ['jquery'],
            '1.0.0',
            true
        );
        wp_localize_script('ap-event-filter', 'APEventFilter', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }

    public static function render(): string
    {
        ob_start();
        ?>
        <form id="ap-event-filter-form" class="ap-event-filter-form">
            <input type="text" name="keyword" placeholder="<?php esc_attr_e('Keyword', 'artpulse'); ?>" />
            <input type="text" name="venue" placeholder="<?php esc_attr_e('Venue', 'artpulse'); ?>" />
            <input type="date" name="after" />
            <input type="date" name="before" />
            <input type="text" name="category" placeholder="<?php esc_attr_e('Category', 'artpulse'); ?>" />
            <button type="submit" class="ap-form-button"><?php esc_html_e('Filter', 'artpulse'); ?></button>
        </form>
        <div id="ap-event-filter-results" class="ap-directory-results" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}

function ap_filter_events_callback(): void
{
    $venue    = sanitize_text_field($_REQUEST['venue'] ?? '');
    $after    = sanitize_text_field($_REQUEST['after'] ?? '');
    $before   = sanitize_text_field($_REQUEST['before'] ?? '');
    $category   = sanitize_text_field($_REQUEST['category'] ?? '');
    $categories = array_map('sanitize_key', array_map('trim', explode(',', $category)));
    $keyword  = sanitize_text_field($_REQUEST['keyword'] ?? '');

    $meta_query = [];
    if ($venue !== '') {
        $meta_query[] = [
            'key'     => 'venue_name',
            'value'   => $venue,
            'compare' => 'LIKE',
        ];
    }
    if ($after !== '') {
        $meta_query[] = [
            'key'     => 'event_start_date',
            'value'   => $after,
            'type'    => 'DATE',
            'compare' => '>=',
        ];
    }
    if ($before !== '') {
        $meta_query[] = [
            'key'     => 'event_end_date',
            'value'   => $before,
            'type'    => 'DATE',
            'compare' => '<=',
        ];
    }

    $tax_query = [];
    if ($category !== '') {
        $tax_query[] = [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $categories,
        ];
    }

    $args = [
        'post_type'      => 'artpulse_event',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ];
    if ($keyword !== '') {
        $args['s'] = $keyword;
    }
    if ($meta_query) {
        $args['meta_query'] = $meta_query;
    }
    if ($tax_query) {
        $args['tax_query'] = $tax_query;
    }

    $query = new \WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            echo ap_get_event_card(get_the_ID());
        }
        wp_reset_postdata();
    } else {
        echo '<div class="ap-empty">' . esc_html__('No events found.', 'artpulse') . '</div>';
    }
    $html = ob_get_clean();
    echo $html;
    wp_die();
}
