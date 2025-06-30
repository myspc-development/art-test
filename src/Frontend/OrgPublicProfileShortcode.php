<?php
namespace ArtPulse\Frontend;

class OrgPublicProfileShortcode {
    public static function register() {
        add_shortcode('ap_org_profile', [self::class, 'render']);
    }

    public static function render($atts = []) {
        $atts = shortcode_atts([
            'id'   => 0,
            'slug' => '',
        ], $atts, 'ap_org_profile');

        $org_id = absint($atts['id']);
        if (!$org_id && $atts['slug']) {
            $post = get_page_by_path(sanitize_title($atts['slug']), OBJECT, 'artpulse_org');
            if ($post) {
                $org_id = $post->ID;
            }
        }

        if (!$org_id) {
            return '<p>' . esc_html__('Organization not found.', 'artpulse-management') . '</p>';
        }

        $published = get_post_meta($org_id, 'ap_org_profile_published', true);
        if ($published !== '1') {
            return '<p>' . esc_html__('Organization profile is private.', 'artpulse-management') . '</p>';
        }

        $org      = get_post($org_id);
        $logo_id  = get_post_meta($org_id, 'ead_org_logo_id', true);
        $banner_id = get_post_meta($org_id, 'ead_org_banner_id', true);
        $logo     = $logo_id ? wp_get_attachment_url($logo_id) : '';
        $banner   = $banner_id ? wp_get_attachment_url($banner_id) : '';
        $tagline  = get_post_meta($org_id, 'ap_org_tagline', true);
        $about    = get_post_meta($org_id, 'ead_org_description', true);
        $theme    = get_post_meta($org_id, 'ap_org_theme_color', true);

        $social_map = [
            'ead_org_facebook_url'  => 'facebook',
            'ead_org_twitter_url'   => 'twitter',
            'ead_org_instagram_url' => 'instagram',
            'ead_org_linkedin_url'  => 'linkedin',
            'ead_org_youtube_url'   => 'youtube',
        ];
        $social = [];
        foreach ($social_map as $meta_key => $name) {
            $url = get_post_meta($org_id, $meta_key, true);
            if ($url) {
                $social[$name] = $url;
            }
        }

        $featured_ids = array_filter(array_map('absint', explode(',', (string) get_post_meta($org_id, 'ap_org_featured_events', true))));

        ob_start();
        ?>
        <div class="ap-org-profile" style="<?php echo $theme ? 'background:' . esc_attr($theme) . ';' : ''; ?>">
            <?php if ($banner): ?>
                <img src="<?php echo esc_url($banner); ?>" class="ap-org-banner" alt="Organization banner">
            <?php endif; ?>
            <?php if ($logo): ?>
                <img src="<?php echo esc_url($logo); ?>" class="ap-org-logo" alt="Organization logo">
            <?php endif; ?>
            <h1><?php echo esc_html($org->post_title); ?></h1>
            <?php if ($tagline): ?><h2><?php echo esc_html($tagline); ?></h2><?php endif; ?>
            <?php if ($about): ?><div class="ap-org-about"><?php echo wpautop(esc_html($about)); ?></div><?php endif; ?>
            <?php if ($social): ?>
                <div class="ap-org-social">
                    <?php foreach ($social as $platform => $url): ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank"><i class="icon-<?php echo esc_attr($platform); ?>"></i></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($featured_ids): ?>
                <div class="ap-org-featured-events">
                    <h3><?php esc_html_e('Featured Events', 'artpulse-management'); ?></h3>
                    <ul>
                        <?php foreach ($featured_ids as $eid): ?>
                            <li><a href="<?php echo esc_url(get_permalink($eid)); ?>"><?php echo esc_html(get_the_title($eid)); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
