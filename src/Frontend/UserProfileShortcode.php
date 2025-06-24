<?php
namespace ArtPulse\Frontend;

class UserProfileShortcode {

    public static function register() {
        add_shortcode('ap_user_profile', [self::class, 'render']);
    }

    public static function render($atts) {
        $atts = shortcode_atts([
            'id'               => get_current_user_id(),
            'show_social'      => 'true',
            'show_membership'  => 'false',
            'show_completeness'=> 'false'
        ], $atts, 'ap_user_profile');

        $show_social      = filter_var($atts['show_social'], FILTER_VALIDATE_BOOLEAN);
        $show_membership  = filter_var($atts['show_membership'], FILTER_VALIDATE_BOOLEAN);
        $show_completeness= filter_var($atts['show_completeness'], FILTER_VALIDATE_BOOLEAN);

        $user_id = intval($atts['id']);
        $user = get_userdata($user_id);

        if (!$user) {
            return '<div class="ap-user-profile-error">User not found.</div>';
        }

        $bio       = get_user_meta($user_id, 'description', true);
        $followers = self::countFollowers($user_id);
        $avatar    = get_user_meta($user_id, 'ap_custom_avatar', true);
        $twitter   = get_user_meta($user_id, 'ap_social_twitter', true);
        $instagram = get_user_meta($user_id, 'ap_social_instagram', true);
        $website   = get_user_meta($user_id, 'ap_social_website', true);

        $country   = get_user_meta($user_id, 'ap_country', true);
        $state     = get_user_meta($user_id, 'ap_state', true);
        $city      = get_user_meta($user_id, 'ap_city', true);

        if ($show_membership) {
            $level   = get_user_meta($user_id, 'ap_membership_level', true) ?: __('Free', 'artpulse');
            $expires = get_user_meta($user_id, 'ap_membership_expires', true);
            $expires = $expires ? date_i18n(get_option('date_format'), intval($expires)) : __('Never', 'artpulse');
        }

        if ($show_completeness) {
            $fields     = [$bio, $avatar, $twitter, $instagram, $website, $country, $state, $city];
            $filled     = 0;
            foreach ($fields as $field) {
                if (!empty($field)) {
                    $filled++;
                }
            }
            $percentage = round($filled / count($fields) * 100);
        }

        ob_start(); ?>
        <div class="ap-user-profile">
            <div class="ap-user-profile-header">
                <img src="<?php echo esc_url($avatar ? $avatar : get_avatar_url($user_id)); ?>" class="ap-user-avatar" alt="User avatar">
                <h2 class="ap-user-name"><?php echo esc_html($user->display_name); ?></h2>
            </div>
            <div class="ap-user-profile-body">
                <?php if ($bio): ?>
                    <p class="ap-user-bio"><?php echo esc_html($bio); ?></p>
                <?php endif; ?>
                <p><strong>Followers:</strong> <?php echo intval($followers); ?></p>
                <?php if ($show_membership): ?>
                    <p class="ap-user-membership">
                        <strong><?php esc_html_e('Membership Level', 'artpulse'); ?>:</strong>
                        <?php echo esc_html($level); ?>
                        <br>
                        <strong><?php esc_html_e('Expires', 'artpulse'); ?>:</strong>
                        <?php echo esc_html($expires); ?>
                    </p>
                <?php endif; ?>

                <?php if ($show_social): ?>
                    <div class="ap-user-social-links">
                        <?php if ($twitter): ?>
                            <p><a href="<?php echo esc_url($twitter); ?>" target="_blank">Twitter</a></p>
                        <?php endif; ?>
                        <?php if ($instagram): ?>
                            <p><a href="<?php echo esc_url($instagram); ?>" target="_blank">Instagram</a></p>
                        <?php endif; ?>
                        <?php if ($website): ?>
                            <p><a href="<?php echo esc_url($website); ?>" target="_blank">Website</a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_completeness): ?>
                    <p class="ap-profile-completeness">
                        <?php printf(esc_html__('Profile completeness: %s%%', 'artpulse'), intval($percentage)); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function countFollowers($user_id) {
        global $wpdb;
        $meta_key = 'ap_following';
        $like = '%' . $wpdb->esc_like($user_id) . '%';
        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->usermeta}
            WHERE meta_key = %s AND meta_value LIKE %s
        ", $meta_key, $like));
    }
} 
