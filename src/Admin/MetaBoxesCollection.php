<?php
namespace ArtPulse\Admin;

class MetaBoxesCollection {
    public static function register(): void {
        add_action('add_meta_boxes_ap_collection', [self::class, 'add_box']);
        add_action('save_post_ap_collection', [self::class, 'save_box'], 10, 2);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('wp_ajax_ap_search_collection_items', [self::class, 'ajax_search_items']);
    }

    public static function add_box(): void {
        add_meta_box(
            'ap_collection_items_box',
            __('Collection Items', 'artpulse'),
            [self::class, 'render_box'],
            'ap_collection',
            'normal',
            'default'
        );
    }

    public static function render_box($post): void {
        wp_nonce_field('ap_collection_items_nonce', 'ap_collection_items_nonce_field');
        $selected = get_post_meta($post->ID, 'ap_collection_items', true);
        if (!is_array($selected)) {
            $selected = [];
        }
        echo '<div id="ap-collection-metabox">';
        echo '<select id="ap-collection-search" class="ap-related-posts" style="width:100%" data-placeholder="' . esc_attr__('Search items...', 'artpulse') . '"></select>';
        echo '<ul id="ap-collection-items">';
        foreach ($selected as $id) {
            $title = get_the_title($id);
            if (!$title) {
                continue;
            }
            $type_obj = get_post_type_object(get_post_type($id));
            $label = $type_obj ? $type_obj->labels->singular_name : '';
            echo '<li data-id="' . esc_attr($id) . '">' . esc_html($title . ($label ? " ($label)" : '')) . '</li>';
        }
        echo '</ul>';
        echo '<input type="hidden" id="ap_collection_items_order" name="ap_collection_items_order" value="' . esc_attr(implode(',', $selected)) . '" />';
        echo '</div>';
    }

    public static function save_box(int $post_id, $post): void {
        if (!isset($_POST['ap_collection_items_nonce_field']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ap_collection_items_nonce_field'])), 'ap_collection_items_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        $items = isset($_POST['ap_collection_items_order']) ? sanitize_text_field(wp_unslash($_POST['ap_collection_items_order'])) : '';
        $items = array_filter(array_map('intval', explode(',', $items)));
        update_post_meta($post_id, 'ap_collection_items', $items);
    }

    public static function enqueue_assets(string $hook): void {
        global $post;
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        $type = $post->post_type ?? ($_GET['post_type'] ?? '');
        if ($type !== 'ap_collection') {
            return;
        }

        wp_enqueue_style(
            'select2-css',
            plugins_url('assets/libs/select2/css/select2.min.css', ARTPULSE_PLUGIN_FILE),
            [],
            '4.1.0-rc.0'
        );
        wp_enqueue_script(
            'select2-js',
            plugins_url('assets/libs/select2/js/select2.min.js', ARTPULSE_PLUGIN_FILE),
            ['jquery'],
            '4.1.0-rc.0',
            true
        );

        wp_enqueue_script(
            'ap-admin-relationship',
            plugins_url('assets/js/admin-relationship.js', ARTPULSE_PLUGIN_FILE),
            ['jquery', 'select2-js'],
            ARTPULSE_VERSION,
            true
        );
        wp_localize_script('ap-admin-relationship', 'apAdminRelationship', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ap_ajax_nonce'),
            'placeholder_text' => __('Search for items...', 'artpulse'),
        ]);

        wp_enqueue_script(
            'ap-admin-collection-metabox',
            plugins_url('assets/js/admin-collection.js', ARTPULSE_PLUGIN_FILE),
            ['jquery', 'ap-admin-relationship', 'jquery-ui-sortable'],
            ARTPULSE_VERSION,
            true
        );
    }

    public static function ajax_search_items(): void {
        if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'ap_ajax_nonce')) {
            wp_send_json_error(['message' => 'Nonce verification failed.'], 403);
            return;
        }

        $term = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $args = [
            'post_type'      => ['artpulse_artwork', 'artpulse_event', 'artpulse_artist'],
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            's'              => $term,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ];

        $query = new \WP_Query($args);
        $results = [];
        foreach ($query->posts as $id) {
            $title = get_the_title($id);
            if (!$title) {
                continue;
            }
            $type_obj = get_post_type_object(get_post_type($id));
            $label = $type_obj ? $type_obj->labels->singular_name : '';
            $results[] = [
                'id'   => $id,
                'text' => $title . ($label ? " ($label)" : ''),
            ];
        }
        wp_send_json_success(['results' => $results]);
    }
}
