<?php
namespace ArtPulse\Frontend;

class ArtistDashboardShortcode {
    public static function register(): void {
        add_shortcode('ap_artist_dashboard', [self::class, 'render']);
    }

    public static function render(array $atts): string {
        if (!is_user_logged_in()) {
            return '<p>' . __('You must be logged in to view this dashboard.', 'artpulse') . '</p>';
        }

        $user_id = get_current_user_id();
        $artworks = get_posts([
            'post_type'      => 'artpulse_artwork',
            'post_status'    => ['publish','pending','draft'],
            'author'         => $user_id,
            'posts_per_page' => -1,
        ]);

        ob_start();
        ?>
        <div class="ap-dashboard">
            <nav class="dashboard-nav">
                <a href="#membership"><span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?></a>
                <a href="#artworks"><span class="dashicons dashicons-format-gallery"></span><?php esc_html_e('Artworks', 'artpulse'); ?></a>
                <a href="#profile"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e('Profile', 'artpulse'); ?></a>
            </nav>

            <section id="membership-section" class="ap-widget">
                <h2 id="membership"><?php _e('Membership', 'artpulse'); ?></h2>
                <div id="ap-membership-info"></div>
            </section>

            <section id="artworks-section" class="ap-widget">
                <h2 id="artworks"><?php _e('Your Artworks', 'artpulse'); ?></h2>
                <?php echo do_shortcode('[ap_submission_form post_type="artpulse_artwork"]'); ?>
                <ul id="ap-artist-artworks" class="ap-artist-artworks">
                    <?php foreach ($artworks as $art) : ?>
                        <li>
                            <?php echo esc_html($art->post_title); ?>
                            <?php $edit = get_edit_post_link($art->ID); if ($edit) : ?>
                                <a href="<?php echo esc_url($edit); ?>" class="ap-edit-artwork">Edit</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section id="profile-section" class="ap-widget">
                <h2 id="profile"><?php _e('Profile', 'artpulse'); ?></h2>
                <?php echo do_shortcode('[ap_profile_edit]'); ?>
            </section>
        </div>
        <?php
        return ob_get_clean();
    }
}
