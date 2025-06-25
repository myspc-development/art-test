<?php
namespace ArtPulse\Admin;

class ShortcodePages
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenu']);
    }

    public static function addMenu(): void
    {
        add_submenu_page(
            'artpulse-settings',
            __('Shortcode Pages', 'artpulse'),
            __('Shortcode Pages', 'artpulse'),
            'manage_options',
            'artpulse-shortcode-pages',
            [self::class, 'render']
        );
    }

    private static function get_page_ids(): array
    {
        return array_map('intval', (array) get_option('ap_shortcode_page_ids', []));
    }

    public static function create_pages(): void
    {
        $map = [
            '[ap_login]'              => __('Login', 'artpulse'),
            '[ap_register]'           => __('Register', 'artpulse'),
            '[ap_user_dashboard]'     => __('Member Dashboard', 'artpulse'),
            '[ap_profile_edit]'       => __('Edit Profile', 'artpulse'),
            '[ap_submit_organization]' => __('Submit Organization', 'artpulse'),
            '[ap_org_dashboard]'      => __('Organization Dashboard', 'artpulse'),
            '[ap_submit_event]'       => __('Submit Event', 'artpulse'),
        ];
        $created = [];
        foreach ($map as $shortcode => $title) {
            $existing = get_posts([
                'post_type'   => 'page',
                'post_status' => 'any',
                's'           => $shortcode,
                'numberposts' => 1,
                'fields'      => 'ids',
            ]);
            if (!empty($existing)) {
                $created[] = $existing[0];
                continue;
            }
            $page_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => $shortcode,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
            if ($page_id && !is_wp_error($page_id)) {
                $created[] = $page_id;
            }
        }
        if (!empty($created)) {
            update_option('ap_shortcode_page_ids', $created);
        }
    }

    public static function toggle_pages(bool $disable): void
    {
        $page_ids = self::get_page_ids();
        if (empty($page_ids)) {
            return;
        }
        $status = $disable ? 'draft' : 'publish';
        foreach ($page_ids as $id) {
            wp_update_post([
                'ID'          => $id,
                'post_status' => $status,
            ]);
        }
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        if (isset($_POST['ap_create_shortcode_pages']) && check_admin_referer('ap_create_shortcode_pages')) {
            self::create_pages();
            echo '<div class="notice notice-success"><p>' . esc_html__('Pages created.', 'artpulse') . '</p></div>';
        }

        if (isset($_POST['ap_enable_shortcode_pages']) && check_admin_referer('ap_toggle_shortcode_pages')) {
            self::toggle_pages(false);
            echo '<div class="notice notice-success"><p>' . esc_html__('Pages enabled.', 'artpulse') . '</p></div>';
        }

        if (isset($_POST['ap_disable_shortcode_pages']) && check_admin_referer('ap_toggle_shortcode_pages')) {
            self::toggle_pages(true);
            echo '<div class="notice notice-success"><p>' . esc_html__('Pages disabled.', 'artpulse') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Shortcode Pages', 'artpulse'); ?></h1>
            <form method="post" style="margin-bottom:10px;">
                <?php wp_nonce_field('ap_create_shortcode_pages'); ?>
                <input type="submit" name="ap_create_shortcode_pages" class="button button-primary" value="<?php esc_attr_e('Create Pages', 'artpulse'); ?>">
            </form>
            <form method="post">
                <?php wp_nonce_field('ap_toggle_shortcode_pages'); ?>
                <input type="submit" name="ap_enable_shortcode_pages" class="button button-secondary" value="<?php esc_attr_e('Enable Pages', 'artpulse'); ?>">
                <input type="submit" name="ap_disable_shortcode_pages" class="button button-secondary" value="<?php esc_attr_e('Disable Pages', 'artpulse'); ?>">
            </form>
        </div>
        <?php
    }
}
