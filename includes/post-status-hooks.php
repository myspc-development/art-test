<?php
add_action('transition_post_status', 'ap_notify_author_on_rejection', 10, 3);

/**
 * Notify authors when their pending submission is rejected.
 *
 * @param string   $new_status New post status.
 * @param string   $old_status Previous post status.
 * @param WP_Post  $post       Post object.
 */
function ap_notify_author_on_rejection($new_status, $old_status, $post) {
    if ($old_status !== 'pending') {
        return;
    }
    if (!in_array($new_status, ['trash', 'rejected'], true)) {
        return;
    }

    $user = get_user_by('id', $post->post_author);
    if (!$user || !is_email($user->user_email)) {
        return;
    }

    $reason  = get_post_meta($post->ID, 'ap_rejection_reason', true);
    $subject = sprintf(
        __('Your submission "%s" was rejected', 'artpulse'),
        $post->post_title
    );
    $message = sprintf(
        __('Your submission "%s" has been rejected.', 'artpulse'),
        $post->post_title
    );
    if ($reason) {
        $message .= "\n\n" . __('Reason:', 'artpulse') . "\n" . $reason;
    }

    wp_mail($user->user_email, $subject, $message);
}
