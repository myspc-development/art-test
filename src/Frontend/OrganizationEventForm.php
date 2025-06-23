<?php

namespace ArtPulse\Frontend;

class OrganizationEventForm {

    public static function register() {
        add_shortcode('ap_submit_event', [self::class, 'render']);
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to submit an event.</p>';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ap_event_nonce']) && wp_verify_nonce($_POST['ap_event_nonce'], 'submit_event')) {
            self::handle_submission();
        }

        ob_start();
        ?>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <form method="post" enctype="multipart/form-data" class="ap-event-form ap-form-container">
            <?php wp_nonce_field('submit_event', 'ap_event_nonce'); ?>

            <label class="ap-form-label" for="ap_org_event_title">Event Title*</label>
            <input class="ap-form-input" id="ap_org_event_title" type="text" name="title" required>

            <label class="ap-form-label" for="ap_org_event_description">Description*</label>
            <textarea class="ap-form-textarea" id="ap_org_event_description" name="description" required></textarea>

            <label class="ap-form-label" for="ap_org_event_date">Event Date*</label>
            <input class="ap-form-input" id="ap_org_event_date" type="date" name="event_date" required>

            <label class="ap-form-label" for="ap_org_event_start_date">Start Date</label>
            <input class="ap-form-input" id="ap_org_event_start_date" type="date" name="event_start_date">

            <label class="ap-form-label" for="ap_org_event_end_date">End Date</label>
            <input class="ap-form-input" id="ap_org_event_end_date" type="date" name="event_end_date">

            <label class="ap-form-label" for="ap_org_event_location">Location*</label>
            <input class="ap-form-input ap-google-autocomplete" id="ap_org_event_location" type="text" name="event_location" required>

            <label class="ap-form-label" for="ap_org_venue_name">Venue Name</label>
            <input class="ap-form-input" id="ap_org_venue_name" type="text" name="venue_name">

            <label class="ap-form-label" for="ap_org_event_street">Street Address</label>
            <input class="ap-form-input" id="ap_org_event_street" type="text" name="event_street_address">

            <label class="ap-form-label" for="ap_org_event_country">Country</label>
            <input class="ap-form-input" id="ap_org_event_country" type="text" name="event_country">

            <label class="ap-form-label" for="ap_org_event_state">State/Province</label>
            <input class="ap-form-input" id="ap_org_event_state" type="text" name="event_state">

            <label class="ap-form-label" for="ap_org_event_city">City</label>
            <input class="ap-form-input" id="ap_org_event_city" type="text" name="event_city">

            <label class="ap-form-label" for="ap_org_event_postcode">Postcode</label>
            <input class="ap-form-input" id="ap_org_event_postcode" type="text" name="event_postcode">

            <input type="hidden" name="address_components" id="ap_org_address_components">

            <label class="ap-form-label" for="ap_org_event_organizer_name">Organizer Name</label>
            <input class="ap-form-input" id="ap_org_event_organizer_name" type="text" name="event_organizer_name">

            <label class="ap-form-label" for="ap_org_event_organizer_email">Organizer Email</label>
            <input class="ap-form-input" id="ap_org_event_organizer_email" type="email" name="event_organizer_email">

            <label class="ap-form-label" for="ap_org_event_type">Event Type</label>
            <select class="ap-form-select" id="ap_org_event_type" name="event_type">
                <option value="">Select Type</option>
                <?php
                $terms = get_terms(['taxonomy' => 'artpulse_event_type', 'hide_empty' => false]);
                foreach ($terms as $term) {
                    echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                }
                ?>
            </select>

            <label class="ap-form-label" for="ap_org_event_banner">Event Banner</label>
            <input class="ap-form-input" id="ap_org_event_banner" type="file" name="event_banner">

            <label class="ap-form-label">
                <input class="ap-form-input" type="checkbox" name="event_featured" value="1"> Request Featured
            </label>

            <button class="ap-form-button" type="submit">Submit Event</button>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function handle_submission() {
        $title = sanitize_text_field($_POST['title']);
        $description = wp_kses_post($_POST['description']);
        $date = sanitize_text_field($_POST['event_date']);
        $start_date = sanitize_text_field($_POST['event_start_date'] ?? '');
        $end_date   = sanitize_text_field($_POST['event_end_date'] ?? '');
        $location = sanitize_text_field($_POST['event_location']);
        $venue_name = sanitize_text_field($_POST['venue_name'] ?? '');
        $street = sanitize_text_field($_POST['event_street_address'] ?? '');
        $country = sanitize_text_field($_POST['event_country'] ?? '');
        $state = sanitize_text_field($_POST['event_state'] ?? '');
        $city = sanitize_text_field($_POST['event_city'] ?? '');
        $postcode = sanitize_text_field($_POST['event_postcode'] ?? '');
        $address_components = sanitize_text_field($_POST['address_components'] ?? '');
        $organizer_name = sanitize_text_field($_POST['event_organizer_name'] ?? '');
        $organizer_email = sanitize_email($_POST['event_organizer_email'] ?? '');
        $type = intval($_POST['event_type']);
        $featured = isset($_POST['event_featured']) ? '1' : '0';

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $description,
            'post_type'    => 'artpulse_event',
            'post_status'  => 'pending',
            'post_author'  => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) return;

        update_post_meta($post_id, '_ap_event_date', $date);
        update_post_meta($post_id, 'event_start_date', $start_date);
        update_post_meta($post_id, 'event_end_date', $end_date);
        update_post_meta($post_id, '_ap_event_location', $location);
        update_post_meta($post_id, 'venue_name', $venue_name);
        update_post_meta($post_id, 'event_street_address', $street);
        update_post_meta($post_id, 'event_country', $country);
        update_post_meta($post_id, 'event_state', $state);
        update_post_meta($post_id, 'event_city', $city);
        update_post_meta($post_id, 'event_postcode', $postcode);
        update_post_meta($post_id, 'address_components', $address_components);
        update_post_meta($post_id, 'event_organizer_name', $organizer_name);
        update_post_meta($post_id, 'event_organizer_email', $organizer_email);
        update_post_meta($post_id, 'event_featured', $featured);

        if ($type) {
            wp_set_post_terms($post_id, [$type], 'artpulse_event_type');
        }

        if (!empty($_FILES['event_banner']['tmp_name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_id = media_handle_upload('event_banner', $post_id);
            if (!is_wp_error($attachment_id)) {
                update_post_meta($post_id, 'event_banner_id', $attachment_id);
            }
        }

        // Admin notification
        $admin_email = get_option('admin_email');
        $subject = 'New Event Submission on ArtPulse';
        $message = sprintf(
            "A new event was submitted:\n\nTitle: %s\n\nBy User ID: %d\n\nEdit: %s",
            $title,
            get_current_user_id(),
            admin_url("post.php?post={$post_id}&action=edit")
        );
        wp_mail($admin_email, $subject, $message);

        // User confirmation
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_subject = 'Thanks for submitting your event';
        $user_message = "Hi {$current_user->display_name},\n\nThanks for submitting your event \"{$title}\". It is now pending review.";
        wp_mail($user_email, $user_subject, $user_message);

        if (function_exists('wc_add_notice')) {
            wc_add_notice('Event submitted successfully!', 'success');
        }
    }
}
