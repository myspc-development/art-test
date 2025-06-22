<?php
namespace ArtPulse\Admin;

class PendingSubmissionsPage
{
    public static function register()
    {
        add_action('admin_menu', [self::class, 'addMenu']);
        add_action('admin_post_ap_reject_submission', [self::class, 'handleRejection']);
    }

    public static function addMenu()
    {
        add_submenu_page(
            'artpulse-dashboard',
            __('Pending Submissions', 'artpulse'),
            __('Pending', 'artpulse'),
            'publish_posts',
            'ap-pending-submissions',
            [self::class, 'render']
        );
    }

    public static function render()
    {
        $posts = get_posts([
            'post_type'   => ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'],
            'post_status' => 'pending',
            'numberposts' => 100,
        ]);

        echo '<div class="wrap"><h1>' . esc_html__('Pending Submissions', 'artpulse') . '</h1>';
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('Title', 'artpulse') . '</th><th>' . esc_html__('Type', 'artpulse') . '</th><th>' . esc_html__('Actions', 'artpulse') . '</th></tr></thead><tbody>';

        if (!$posts) {
            echo '<tr><td colspan="3">' . esc_html__('No pending submissions.', 'artpulse') . '</td></tr>';
        } else {
            foreach ($posts as $post) {
                $approve_url = wp_nonce_url(admin_url('admin-post.php?action=ap_approve_submission'), 'ap_approve_' . $post->ID);
                $reject_url  = wp_nonce_url(admin_url('admin-post.php?action=ap_reject_submission'), 'ap_reject_' . $post->ID);
                echo '<tr>';
                echo '<td><a href="' . esc_url(get_edit_post_link($post->ID)) . '">' . esc_html(get_the_title($post)) . '</a></td>';
                echo '<td>' . esc_html($post->post_type) . '</td>';
                echo '<td>';
                echo '<form method="post" action="' . esc_url($approve_url) . '" style="display:inline">';
                echo '<input type="hidden" name="post_id" value="' . intval($post->ID) . '" />';
                echo '<input type="hidden" name="nonce" value="' . wp_create_nonce('ap_approve_' . $post->ID) . '" />';
                submit_button(__('Approve', 'artpulse'), 'primary', 'submit', false);
                echo '</form> ';
                echo '<form method="post" action="' . esc_url($reject_url) . '" style="display:inline">';
                echo '<input type="hidden" name="post_id" value="' . intval($post->ID) . '" />';
                echo '<input type="hidden" name="nonce" value="' . wp_create_nonce('ap_reject_' . $post->ID) . '" />';
                submit_button(__('Reject', 'artpulse'), 'secondary', 'submit', false, ['onclick' => 'return confirm("' . esc_js(__('Are you sure?', 'artpulse')) . '");']);
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table></div>';
    }

    public static function handleRejection()
    {
        if (!current_user_can('delete_posts')) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $nonce   = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'ap_reject_' . $post_id)) {
            wp_die(__('Security check failed', 'artpulse'));
        }
        wp_trash_post($post_id);
        wp_safe_redirect(admin_url('admin.php?page=ap-pending-submissions'));
        exit;
    }
}
