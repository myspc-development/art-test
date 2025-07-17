<?php
namespace ArtPulse\Admin;

use ArtPulse\Core\MembershipManager;

class MembershipLevelsPage
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Membership Levels', 'artpulse'),
            __('Membership Levels', 'artpulse'),
            'manage_options',
            'ap-membership-levels',
            [self::class, 'render']
        );
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        $levels = MembershipManager::getLevels();

        if (isset($_POST['ap_add_level']) && check_admin_referer('ap_add_level')) {
            $level = sanitize_text_field($_POST['new_level']);
            if ($level) {
                $levels[] = $level;
                $levels   = array_values(array_unique($levels));
                update_option('ap_membership_levels', $levels);
                echo '<div class="notice notice-success"><p>' . esc_html__('Level added.', 'artpulse') . '</p></div>';
            }
        }

        if (isset($_GET['delete']) && check_admin_referer('ap_del_level')) {
            $del    = sanitize_text_field($_GET['delete']);
            $levels = array_values(array_diff($levels, [$del]));
            update_option('ap_membership_levels', $levels);
            echo '<div class="notice notice-success"><p>' . esc_html__('Level deleted.', 'artpulse') . '</p></div>';
        }

        echo '<div class="wrap"><h1>' . esc_html__('Membership Levels', 'artpulse') . '</h1>';
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('Level', 'artpulse') . '</th><th>' . esc_html__('Actions', 'artpulse') . '</th></tr></thead><tbody>';
        foreach ($levels as $level) {
            $url = wp_nonce_url(admin_url('admin.php?page=ap-membership-levels&delete=' . urlencode($level)), 'ap_del_level');
            echo '<tr><td>' . esc_html($level) . '</td><td><a href="' . esc_url($url) . '">' . esc_html__('Delete', 'artpulse') . '</a></td></tr>';
        }
        echo '</tbody></table>';
        echo '<h3>' . esc_html__('Add Level', 'artpulse') . '</h3>';
        echo '<form method="post">';
        wp_nonce_field('ap_add_level');
        echo '<input type="text" name="new_level" required /> ';
        echo '<input type="submit" name="ap_add_level" class="button button-primary" value="' . esc_attr__('Add', 'artpulse') . '" />';
        echo '</form></div>';
    }
}
