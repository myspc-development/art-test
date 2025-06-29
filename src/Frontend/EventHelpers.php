<?php
namespace ArtPulse\Frontend;

/**
 * Helper functions for rendering RSVP and favorite buttons.
 */

function ap_render_rsvp_button(int $event_id): string
{
    $rsvp_count = intval(get_post_meta($event_id, 'ap_rsvp_count', true));
    $user_id = get_current_user_id();
    $rsvps = $user_id ? (array) get_user_meta($user_id, 'ap_rsvp_events', true) : [];
    $joined = in_array($event_id, $rsvps, true);

    $label = $joined ? __('Cancel RSVP', 'artpulse') : __('RSVP', 'artpulse');
    ob_start();
    ?>
    <button class="ap-rsvp-btn<?php echo $joined ? ' ap-rsvped' : ''; ?> ap-form-button" data-event="<?php echo esc_attr($event_id); ?>"><?php echo esc_html($label); ?></button>
    <span class="ap-rsvp-count"><?php echo esc_html($rsvp_count); ?></span>
    <?php
    return trim(ob_get_clean());
}

function ap_render_favorite_button(int $event_id): string
{
    $fav_count = intval(get_post_meta($event_id, 'ap_favorite_count', true));
    $user_id = get_current_user_id();
    $favorited = $user_id && function_exists('ap_user_has_favorited') ? ap_user_has_favorited($user_id, $event_id) : false;
    $star = $favorited ? '★' : '☆';
    ob_start();
    ?>
    <button class="ap-fav-btn<?php echo $favorited ? ' ap-favorited' : ''; ?>" data-post="<?php echo esc_attr($event_id); ?>"><?php echo $star; ?></button>
    <span class="ap-fav-count"><?php echo esc_html($fav_count); ?></span>
    <?php
    return trim(ob_get_clean());
}
