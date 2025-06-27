<?php
namespace ArtPulse\Frontend;

class ArtistDashboardShortcode {
    public static function register(): void {
        add_shortcode('ap_artist_dashboard', [self::class, 'render']);
        add_action('wp_ajax_ap_delete_artwork', [self::class, 'handle_ajax_delete_artwork']);
        add_action('wp_ajax_save_artwork_order', [self::class, 'handle_save_artwork_order']);
    }

    public static function render(): string {
        if (!is_user_logged_in()) {
            return '<p>' . __('You must be logged in to view this dashboard.', 'artpulse') . '</p>';
        }

        // Fetch artworks by current user
        $artworks = get_posts([
            'post_type'   => 'artpulse_artwork',
            'author'      => get_current_user_id(),
            'post_status' => ['publish', 'pending', 'draft'],
            'numberposts' => -1,
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
        ]);

        // Build metrics array keyed by artwork ID
        $metrics = [];
        foreach ($artworks as $artwork) {
            $metrics[$artwork->ID] = [
                'views'  => (int) get_post_meta($artwork->ID, 'view_count', true),
                'likes'  => (int) get_post_meta($artwork->ID, 'like_count', true),
                'shares' => (int) get_post_meta($artwork->ID, 'share_count', true),
            ];
        }

        ob_start();
        ?>
        <div class="ap-dashboard ap-artist-dashboard">
            <h1><?php esc_html_e('Artist Dashboard', 'artpulse'); ?></h1>

            <h2><?php esc_html_e('Your Artworks', 'artpulse'); ?></h2>
            <ul class="ap-artwork-list">
                <?php foreach ($artworks as $artwork) : ?>
                    <li>
                        <a href="<?php echo esc_url(get_permalink($artwork)); ?>">
                            <?php echo esc_html(get_the_title($artwork)); ?>
                        </a>
                        <?php $edit = get_edit_post_link($artwork->ID); if ($edit) : ?>
                            <a href="<?php echo esc_url($edit); ?>" class="ap-edit-artwork">Edit</a>
                        <?php endif; ?>
                        <button class="ap-delete-artwork" data-id="<?php echo intval($artwork->ID); ?>">Delete</button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h2 id="upload-artwork"><?php esc_html_e('Upload New Artwork', 'artpulse'); ?></h2>
            <form class="ap-artwork-upload-form ap-form-container" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-title"><?php esc_html_e('Title', 'artpulse'); ?></label>
                    <input class="ap-input" id="ap-artwork-title" type="text" name="title" required />
                </div>
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-medium"><?php esc_html_e('Medium', 'artpulse'); ?></label>
                    <input class="ap-input" id="ap-artwork-medium" type="text" name="artwork_medium" />
                </div>
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-dimensions"><?php esc_html_e('Dimensions', 'artpulse'); ?></label>
                    <input class="ap-input" id="ap-artwork-dimensions" type="text" name="artwork_dimensions" />
                </div>
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-materials"><?php esc_html_e('Materials', 'artpulse'); ?></label>
                    <input class="ap-input" id="ap-artwork-materials" type="text" name="artwork_materials" />
                </div>
                <?php if (get_option('ap_enable_artworks_for_sale')) : ?>
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-for-sale">
                        <input type="checkbox" id="ap-artwork-for-sale" name="for_sale" value="1" />
                        <?php esc_html_e('For Sale?', 'artpulse'); ?>
                    </label>
                </div>
                <div class="ap-sale-fields" style="display:none;">
                    <div class="form-group">
                        <label class="ap-form-label" for="ap-artwork-price"><?php esc_html_e('Price', 'artpulse'); ?></label>
                        <input class="ap-input" id="ap-artwork-price" type="text" name="price" />
                    </div>
                    <div class="form-group">
                        <label class="ap-form-label" for="ap-artwork-buy-link"><?php esc_html_e('Buy Link', 'artpulse'); ?></label>
                        <input class="ap-input" id="ap-artwork-buy-link" type="url" name="buy_link" />
                    </div>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label>
                    <input class="ap-input" id="ap-artwork-images" type="file" name="images[]" accept="image/*" multiple />
                </div>
                <div class="form-group">
                    <button class="ap-form-button nectar-button" type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
                </div>
            </form>
            <div class="ap-form-messages" role="status" aria-live="polite"></div>
        </div>
        <?php
        wp_enqueue_script('ap-artwork-submission-js');
        wp_enqueue_script('ap-artist-dashboard');
        wp_localize_script('ap-artist-dashboard', 'APArtistMetrics', $metrics);
        return ob_get_clean();
    }

    public static function handle_ajax_delete_artwork(): void {
        check_ajax_referer('ap_artist_dashboard_nonce', 'nonce');

        $artwork_id = intval($_POST['artwork_id'] ?? 0);
        if (!$artwork_id || !current_user_can('delete_post', $artwork_id)) {
            wp_send_json_error(['message' => 'Insufficient permissions.']);
        }

        $post = get_post($artwork_id);
        if (!$post || $post->post_type !== 'artpulse_artwork' || $post->post_author != get_current_user_id()) {
            wp_send_json_error(['message' => 'Invalid artwork.']);
        }

        wp_delete_post($artwork_id, true);

        ob_start();
        $artworks = get_posts([
            'post_type'   => 'artpulse_artwork',
            'author'      => get_current_user_id(),
            'post_status' => ['publish','pending','draft'],
            'numberposts' => -1,
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
        ]);
        foreach ($artworks as $artwork) {
            $edit = get_edit_post_link($artwork->ID);
            echo '<li><a href="' . esc_url(get_permalink($artwork)) . '">' . esc_html(get_the_title($artwork)) . '</a>';
            if ($edit) {
                echo ' <a href="' . esc_url($edit) . '" class="ap-edit-artwork">Edit</a>';
            }
            echo ' <button class="ap-delete-artwork" data-id="' . $artwork->ID . '">Delete</button></li>';
        }
        $html = ob_get_clean();

        wp_send_json_success(['updated_list_html' => $html]);
    }

    public static function handle_save_artwork_order(): void {
        check_ajax_referer('ap_artist_dashboard_nonce', 'nonce');

        $order = isset($_POST['order']) ? json_decode(stripslashes($_POST['order']), true) : [];
        if (!is_array($order)) {
            wp_send_json_error(['message' => 'Invalid order']);
        }

        foreach ($order as $index => $art_id) {
            $art_id = intval($art_id);
            if ($art_id && current_user_can('edit_post', $art_id)) {
                wp_update_post([
                    'ID'         => $art_id,
                    'menu_order' => $index,
                ]);
            }
        }

        wp_send_json_success();
    }
}
