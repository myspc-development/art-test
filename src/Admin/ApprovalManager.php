<?php

namespace ArtPulse\Admin;

/**
 * Handles admin approval workflow for pending submissions.
 */
class ApprovalManager
{
    /**
     * Register approval hooks and meta boxes.
     */
    public static function register()
    {
        // Add meta box to relevant CPTs
        add_action('add_meta_boxes', [ __CLASS__, 'addApprovalMetabox' ]);
        // Handle approval and rejection actions
        add_action('admin_post_ap_approve_submission', [ __CLASS__, 'handleApproval' ]);
        add_action('admin_post_ap_reject_submission', [ __CLASS__, 'handleRejection' ]);
    }

    /**
     * Add an "Approval" meta box on pending posts of our CPTs.
     */
    public static function addApprovalMetabox()
    {
        $post_types = ['artpulse_event', 'artpulse_artist', 'artpulse_artwork', 'artpulse_org'];
        foreach ($post_types as $pt) {
            add_meta_box(
                'ap-approval',
                __('Submission Approval', 'artpulse'),
                [ __CLASS__, 'renderMetabox' ],
                $pt,
                'side',
                'high'
            );
        }
    }

    /**
     * Render the Approval meta box.
     */
    public static function renderMetabox($post)
    {
        if ('pending' !== $post->post_status) {
            echo '<p>' . __('This submission is already reviewed.', 'artpulse') . '</p>';
            return;
        }
        $approve_url   = admin_url('admin-post.php');
        $approve_nonce = wp_create_nonce('ap_approve_' . $post->ID);
        $reject_nonce  = wp_create_nonce('ap_reject_' . $post->ID);
        ?>
        <form method="post" action="<?php echo esc_url($approve_url); ?>">
            <input type="hidden" name="action" value="ap_approve_submission" />
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post->ID); ?>" />
            <input type="hidden" name="nonce" value="<?php echo esc_attr($approve_nonce); ?>" />
            <?php submit_button(__('Approve', 'artpulse'), 'primary', 'submit', false); ?>
        </form>
        <form method="post" action="<?php echo esc_url($approve_url); ?>">
            <input type="hidden" name="action" value="ap_reject_submission" />
            <input type="hidden" name="post_id" value="<?php echo esc_attr($post->ID); ?>" />
            <input type="hidden" name="nonce" value="<?php echo esc_attr($reject_nonce); ?>" />
            <?php submit_button(__('Reject', 'artpulse'), 'secondary', 'submit', false); ?>
        </form>
        <?php
    }

    /**
     * Handle the approval request.
     */
    public static function handleApproval()
    {
        if ( ! current_user_can('publish_posts') ) {
            wp_die(__('Insufficient permissions', 'artpulse'));        }
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $nonce   = $_POST['nonce'] ?? '';
        if ( ! wp_verify_nonce($nonce, 'ap_approve_' . $post_id) ) {
            wp_die(__('Security check failed', 'artpulse'));        }
        $post = get_post($post_id);
        if ($post && in_array($post->post_type, ['ap_artist_request', 'artpulse_artist'], true)) {
            $user = get_user_by('id', $post->post_author);
            if ($user && !in_array('artist', (array) $user->roles, true)) {
                if (in_array('administrator', (array) $user->roles, true)) {
                    // Preserve admin role when granting Artist capabilities
                    $user->add_role('artist');
                } else {
                    $user->set_role('artist');
                }
            }
        }

        wp_update_post([
            'ID'          => $post_id,
            'post_status' => 'publish',
        ]);
        if ($post && $post->post_type === 'artpulse_org') {
            update_user_meta($post->post_author, 'ap_organization_id', $post_id);
            delete_user_meta($post->post_author, 'ap_pending_organization_id');
        }
        // Redirect back to edit screen
        wp_safe_redirect(admin_url('post.php?post=' . $post_id . '&action=edit'));
        exit;
    }

    /**
     * Handle rejection request.
     */
    public static function handleRejection()
    {
        if ( ! current_user_can('delete_posts') ) {
            wp_die(__('Insufficient permissions', 'artpulse'));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $nonce   = $_POST['nonce'] ?? '';
        if ( ! wp_verify_nonce($nonce, 'ap_reject_' . $post_id) ) {
            wp_die(__('Security check failed', 'artpulse'));
        }

        wp_trash_post($post_id);

        wp_safe_redirect(admin_url('admin.php?page=ap-pending-submissions'));
        exit;
    }
}
