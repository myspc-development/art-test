<?php
namespace ArtPulse\Admin;

class MetaBoxesCollection {
    public static function register(): void {
        add_action('add_meta_boxes_ap_collection', [self::class, 'add_box']);
        add_action('save_post_ap_collection', [self::class, 'save_box'], 10, 2);
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
        $events = get_posts([
            'post_type'      => 'artpulse_event',
            'post_status'    => 'publish',
            'numberposts'    => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        $artworks = get_posts([
            'post_type'      => 'artpulse_artwork',
            'post_status'    => 'publish',
            'numberposts'    => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ]);
        echo '<select name="ap_collection_items[]" multiple="multiple" style="width:100%;height:150px;">';
        foreach ($events as $id) {
            $sel = in_array($id, $selected, true) ? 'selected="selected"' : '';
            printf('<option value="%d" %s>%s (Event)</option>', $id, $sel, esc_html(get_the_title($id)));
        }
        foreach ($artworks as $id) {
            $sel = in_array($id, $selected, true) ? 'selected="selected"' : '';
            printf('<option value="%d" %s>%s (Artwork)</option>', $id, $sel, esc_html(get_the_title($id)));
        }
        echo '</select>';
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
        $items = isset($_POST['ap_collection_items']) ? (array) $_POST['ap_collection_items'] : [];
        $items = array_map('intval', $items);
        update_post_meta($post_id, 'ap_collection_items', $items);
    }
}
