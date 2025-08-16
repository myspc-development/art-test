<?php
if (!defined('ABSPATH')) {
    exit;
}

function ap_artist_portfolio_metaboxes_register() {
    add_meta_box(
        'ap_artist_badges',
        __('Artist Badges', 'artpulse'),
        'ap_render_artist_badges_metabox',
        'artpulse_portfolio',
        'side'
    );
    add_meta_box(
        'ap_artist_gallery',
        __('Gallery Images', 'artpulse'),
        'ap_render_artist_gallery_metabox',
        'artpulse_portfolio',
        'normal'
    );
}

function ap_render_artist_badges_metabox($post) {
    $featured  = get_post_meta($post->ID, 'artist_featured', true);
    $spotlight = get_post_meta($post->ID, 'artist_spotlight', true);
    wp_nonce_field('ap_artist_meta_nonce', 'ap_artist_meta_nonce_field');
    echo '<p><label><input type="checkbox" name="artist_featured" value="1" ' . checked($featured, '1', false) . ' /> ' . esc_html__('Featured', 'artpulse') . '</label></p>';
    echo '<p><label><input type="checkbox" name="artist_spotlight" value="1" ' . checked($spotlight, '1', false) . ' /> ' . esc_html__('Spotlight', 'artpulse') . '</label></p>';
}

function ap_render_artist_gallery_metabox($post) {
    wp_enqueue_media();

    $ids = get_post_meta($post->ID, '_ap_submission_images', true);
    if (!is_array($ids)) {
        $ids = [];
    }
    $value = implode(',', $ids);

    echo '<div id="artist-gallery-container">';
    foreach ($ids as $id) {
        echo wp_get_attachment_image($id, 'thumbnail', false, ['style' => 'margin-right:5px;']);
    }
    echo '</div>';
    echo '<input type="hidden" id="artist_gallery_ids" name="artist_gallery_ids" value="' . esc_attr($value) . '" />';
    echo '<button type="button" class="button" id="artist_gallery_upload">' . esc_html__('Select Images', 'artpulse') . '</button>';
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($){
        var frame;
        $('#artist_gallery_upload').on('click', function(e){
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: '<?php echo esc_js(__('Select Images', 'artpulse')); ?>',
                button: { text: '<?php echo esc_js(__('Use images', 'artpulse')); ?>' },
                multiple: true
            });
            frame.on('select', function(){
                var selection = frame.state().get('selection');
                var ids = [];
                var container = $('#artist-gallery-container').empty();
                selection.each(function(attachment){
                    ids.push(attachment.id);
                    var url = attachment.attributes.sizes && attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.url;
                    container.append('<img src="'+url+'" style="margin-right:5px;" />');
                });
                $('#artist_gallery_ids').val(ids.join(','));
            });
            frame.open();
        });
    });
    </script>
    <?php
}

function ap_save_artist_portfolio_meta($post_id, $post) {
    if ($post->post_type !== 'artpulse_portfolio') {
        return;
    }
    if (!isset($_POST['ap_artist_meta_nonce_field']) || !wp_verify_nonce($_POST['ap_artist_meta_nonce_field'], 'ap_artist_meta_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    update_post_meta($post_id, 'artist_featured', isset($_POST['artist_featured']) ? '1' : '0');
    update_post_meta($post_id, 'artist_spotlight', isset($_POST['artist_spotlight']) ? '1' : '0');

    if (isset($_POST['artist_gallery_ids'])) {
        $ids = array_filter(array_map('intval', explode(',', sanitize_text_field($_POST['artist_gallery_ids']))));
        update_post_meta($post_id, '_ap_submission_images', $ids);
        if ($ids) {
            set_post_thumbnail($post_id, $ids[0]);
        }
    }
}
