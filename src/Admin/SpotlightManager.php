<?php
namespace ArtPulse\Admin;

class SpotlightManager
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'add_menu']);
    }

    public static function add_menu(): void
    {
        add_submenu_page(
            'artpulse-dashboard',
            __('Artist Spotlights', 'artpulse'),
            __('Spotlights', 'artpulse'),
            'edit_artpulse_artists',
            'ap-spotlights',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        if (!current_user_can('edit_artpulse_artists')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        if (isset($_POST['ap_spotlights_nonce']) && wp_verify_nonce($_POST['ap_spotlights_nonce'], 'ap_spotlights_save')) {
            $ids    = array_map('intval', $_POST['artist_id'] ?? []);
            $starts = $_POST['spotlight_start'] ?? [];
            $ends   = $_POST['spotlight_end'] ?? [];
            foreach ($ids as $id) {
                $spot = isset($_POST['spotlight'][$id]) ? '1' : '0';
                update_post_meta($id, 'artist_spotlight', $spot);
                update_post_meta($id, 'spotlight_start_date', sanitize_text_field($starts[$id] ?? ''));
                update_post_meta($id, 'spotlight_end_date', sanitize_text_field($ends[$id] ?? ''));
            }
            echo '<div class="notice notice-success"><p>' . esc_html__('Spotlights updated.', 'artpulse') . '</p></div>';
        }

        $artists = get_posts([
            'post_type'      => 'artpulse_artist',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        echo '<div class="wrap"><h1>' . esc_html__('Artist Spotlights', 'artpulse') . '</h1>';
        echo '<form method="post">';
        wp_nonce_field('ap_spotlights_save', 'ap_spotlights_nonce');
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('Artist', 'artpulse') . '</th><th>' . esc_html__('Spotlight', 'artpulse') . '</th><th>' . esc_html__('Start', 'artpulse') . '</th><th>' . esc_html__('End', 'artpulse') . '</th></tr></thead><tbody>';
        foreach ($artists as $artist) {
            $spot  = get_post_meta($artist->ID, 'artist_spotlight', true);
            $start = get_post_meta($artist->ID, 'spotlight_start_date', true);
            $end   = get_post_meta($artist->ID, 'spotlight_end_date', true);
            echo '<tr>';
            echo '<td>' . esc_html($artist->post_title) . '<input type="hidden" name="artist_id[]" value="' . intval($artist->ID) . '" /></td>';
            echo '<td><input type="checkbox" name="spotlight[' . intval($artist->ID) . ']" value="1" ' . checked($spot, '1', false) . ' /></td>';
            echo '<td><input type="date" name="spotlight_start[' . intval($artist->ID) . ']" value="' . esc_attr($start) . '" /></td>';
            echo '<td><input type="date" name="spotlight_end[' . intval($artist->ID) . ']" value="' . esc_attr($end) . '" /></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        submit_button(__('Save Spotlights', 'artpulse'));
        echo '</form></div>';
    }

    /**
     * Retrieve spotlight posts for a dashboard role view.
     */
    public static function get_dashboard_spotlights(string $role, ?string $category = null): array
    {
        $today = current_time('Y-m-d');

        $args = [
            'post_type'      => 'spotlight',
            'posts_per_page' => 5,
            'tax_query'      => [],
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => 'visible_to_roles',
                    'value'   => $role,
                    'compare' => 'LIKE',
                ],
                [
                    'key'     => 'start_at',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ],
                [
                    'key'     => 'expires_at',
                    'value'   => $today,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ],
            ],
            'orderby'   => [ 'meta_value_num' => 'DESC', 'date' => 'DESC' ],
            'meta_key'  => 'is_pinned',
        ];

        if ($category) {
            $args['tax_query'][] = [
                'taxonomy' => 'spotlight_category',
                'field'    => 'slug',
                'terms'    => [$category],
            ];
        }

        return get_posts($args);
    }
}
