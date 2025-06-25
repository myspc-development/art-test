<?php
namespace ArtPulse\Frontend;

class ArtistDashboardShortcode {
    public static function register(): void {
        add_shortcode('ap_artist_dashboard', [self::class, 'render']);
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
        ]);

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
                <div class="form-group">
                    <label class="ap-form-label" for="ap-artwork-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label>
                    <input class="ap-input" id="ap-artwork-images" type="file" name="images[]" accept="image/*" multiple />
                </div>
                <div class="form-group">
                    <button class="ap-form-button" type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
                </div>
            </form>
            <div class="ap-form-messages" role="status" aria-live="polite"></div>
        </div>
        <?php
        wp_enqueue_script('ap-artwork-submission-js');
        return ob_get_clean();
    }
}
