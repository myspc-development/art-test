<?php

namespace ArtPulse\Frontend;

class OrganizationEventForm {

    public static function register() {
        // Use a unique shortcode to avoid clashing with EventSubmissionShortcode
        add_shortcode('ap_org_event_form', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_styles']);
    }

    public static function enqueue_styles(): void {
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to submit an event.</p>';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ap_event_nonce']) && wp_verify_nonce($_POST['ap_event_nonce'], 'submit_event')) {
            self::handle_submission();
        }

        $opts         = get_option('artpulse_settings', []);
        $limit_default= isset($opts['default_rsvp_limit']) ? absint($opts['default_rsvp_limit']) : 50;
        $limit_min    = absint($opts['min_rsvp_limit'] ?? 0);
        $limit_max    = absint($opts['max_rsvp_limit'] ?? 0);
        $wait_default = !empty($opts['waitlists_enabled']);

        ob_start();
        ?>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <form method="post" enctype="multipart/form-data" class="ap-form-container" data-no-ajax="true">
            <?php wp_nonce_field('submit_event', 'ap_event_nonce'); ?>

            <label class="ap-form-label" for="ap_org_event_title">Event Title*</label>
            <input class="ap-input" id="ap_org_event_title" type="text" name="title" required>

            <label class="ap-form-label" for="ap_org_event_description">Description*</label>
            <textarea class="ap-input" id="ap_org_event_description" name="description" required></textarea>

            <label class="ap-form-label" for="ap_org_event_date">Event Date*</label>
            <input class="ap-input" id="ap_org_event_date" type="date" name="event_date" required>

            <label class="ap-form-label" for="ap_org_event_start_date">Start Date</label>
            <input class="ap-input" id="ap_org_event_start_date" type="date" name="event_start_date">

            <label class="ap-form-label" for="ap_org_event_end_date">End Date</label>
            <input class="ap-input" id="ap_org_event_end_date" type="date" name="event_end_date">

            <label class="ap-form-label" for="ap_org_event_start_time">Start Time</label>
            <input class="ap-input" id="ap_org_event_start_time" type="time" name="event_start_time">

            <label class="ap-form-label" for="ap_org_event_end_time">End Time</label>
            <input class="ap-input" id="ap_org_event_end_time" type="time" name="event_end_time">

            <label class="ap-form-label" for="ap_org_event_location">Location*</label>
            <input class="ap-input ap-google-autocomplete" id="ap_org_event_location" type="text" name="event_location" required>

            <label class="ap-form-label" for="ap_org_venue_name">Venue Name</label>
            <input class="ap-input" id="ap_org_venue_name" type="text" name="venue_name">

            <label class="ap-form-label" for="ap_org_event_street">Street Address</label>
            <input class="ap-input" id="ap_org_event_street" type="text" name="event_street_address">

            <label class="ap-form-label" for="ap_org_event_country">Country</label>
            <input class="ap-input" id="ap_org_event_country" type="text" name="event_country">

            <label class="ap-form-label" for="ap_org_event_state">State/Province</label>
            <input class="ap-input" id="ap_org_event_state" type="text" name="event_state">

            <label class="ap-form-label" for="ap_org_event_city">City</label>
            <input class="ap-input" id="ap_org_event_city" type="text" name="event_city">

            <label class="ap-form-label" for="ap_org_event_postcode">Postcode</label>
            <input class="ap-input" id="ap_org_event_postcode" type="text" name="event_postcode">

            <input type="hidden" name="address_components" id="ap_org_address_components">

            <label class="ap-form-label" for="ap_org_event_address">Address</label>
            <input class="ap-input" id="ap_org_event_address" type="text" name="event_address">

            <label class="ap-form-label" for="ap_org_event_contact">Contact Info</label>
            <input class="ap-input" id="ap_org_event_contact" type="text" name="event_contact">

            <label class="ap-form-label" for="ap_org_event_rsvp">RSVP URL</label>
            <input class="ap-input" id="ap_org_event_rsvp" type="url" name="event_rsvp_url">

            <label class="ap-form-label" for="ap_org_event_organizer_name">Organizer Name</label>
            <input class="ap-input" id="ap_org_event_organizer_name" type="text" name="event_organizer_name">

            <label class="ap-form-label" for="ap_org_event_organizer_email">Organizer Email</label>
            <input class="ap-input" id="ap_org_event_organizer_email" type="email" name="event_organizer_email">

            <label class="ap-form-label" for="ap_org_event_type">Event Type</label>
            <select class="ap-input" id="ap_org_event_type" name="event_type">
                <option value="">Select Type</option>
                <?php
                $terms = get_terms(['taxonomy' => 'event_type', 'hide_empty' => false]);
                foreach ($terms as $term) {
                    echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                }
                ?>
            </select>

            <label class="ap-form-label" for="ap_org_event_banner">Event Banner</label>
            <input class="ap-input" id="ap_org_event_banner" type="file" name="event_banner">

            <label class="ap-form-label" for="ap_org_event_images">Additional Images (max 5)</label>
            <input class="ap-input" id="ap_org_event_images" type="file" name="images[]" multiple>

            <label class="ap-form-label">
                <input class="ap-input" type="checkbox" name="event_rsvp_enabled" value="1"> Enable RSVP
            </label>

            <label class="ap-form-label" for="ap_org_event_rsvp_limit">RSVP Limit</label>
            <input class="ap-input" id="ap_org_event_rsvp_limit" type="number" name="event_rsvp_limit" value="<?php echo esc_attr($limit_default); ?>"<?php if ($limit_min) echo ' min="' . esc_attr($limit_min) . '"'; ?><?php if ($limit_max) echo ' max="' . esc_attr($limit_max) . '"'; ?>>

            <?php if ($wait_default) : ?>
            <label class="ap-form-label">
                <input class="ap-input" type="checkbox" name="event_waitlist_enabled" value="1" checked> Enable Waitlist
            </label>
            <?php else : ?>
            <input type="hidden" name="event_waitlist_enabled" value="0">
            <?php endif; ?>

            <label class="ap-form-label">
                <input class="ap-input" type="checkbox" name="event_featured" value="1"> Request Featured
            </label>

            <button class="ap-form-button nectar-button" type="submit">Submit Event</button>
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
        $address_json = wp_unslash($_POST['address_components'] ?? '');
        $address_components = json_decode($address_json, true);
        $address_full = sanitize_text_field($_POST['event_address'] ?? '');
        $start_time = sanitize_text_field($_POST['event_start_time'] ?? '');
        $end_time = sanitize_text_field($_POST['event_end_time'] ?? '');
        $contact_info = sanitize_text_field($_POST['event_contact'] ?? '');
        $rsvp_url = sanitize_text_field($_POST['event_rsvp_url'] ?? '');
        $organizer_name = sanitize_text_field($_POST['event_organizer_name'] ?? '');
        $organizer_email = sanitize_email($_POST['event_organizer_email'] ?? '');
        $type = intval($_POST['event_type']);
        $featured = isset($_POST['event_featured']) ? '1' : '0';
        $rsvp_enabled = isset($_POST['event_rsvp_enabled']) ? '1' : '0';
        $rsvp_limit   = isset($_POST['event_rsvp_limit']) ? intval($_POST['event_rsvp_limit']) : 0;
        $waitlist_enabled = isset($_POST['event_waitlist_enabled']) ? '1' : '0';

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
        if (is_array($address_components)) {
            update_post_meta($post_id, 'address_components', wp_json_encode($address_components));
        }
        update_post_meta($post_id, '_ap_event_address', $address_full);
        update_post_meta($post_id, '_ap_event_start_time', $start_time);
        update_post_meta($post_id, '_ap_event_end_time', $end_time);
        update_post_meta($post_id, '_ap_event_contact', $contact_info);
        update_post_meta($post_id, '_ap_event_rsvp', $rsvp_url);
        update_post_meta($post_id, 'event_organizer_name', $organizer_name);
        update_post_meta($post_id, 'event_organizer_email', $organizer_email);
        update_post_meta($post_id, 'event_featured', $featured);
        update_post_meta($post_id, 'event_rsvp_enabled', $rsvp_enabled);
        update_post_meta($post_id, 'event_rsvp_limit', $rsvp_limit);
        update_post_meta($post_id, 'event_waitlist_enabled', $waitlist_enabled);

        if ($type) {
            wp_set_post_terms($post_id, [$type], 'event_type');
        }

        $image_ids = [];
        $image_order = [];
        if ( isset( $_POST['image_order'] ) ) {
            $image_order = array_map( 'intval', array_filter( explode( ',', (string) $_POST['image_order'] ) ) );
        }

        if (! function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        if (!empty($_FILES['event_banner']['tmp_name'])) {
            $attachment_id = media_handle_upload('event_banner', $post_id);
            if (!is_wp_error($attachment_id)) {
                $image_ids[] = $attachment_id;
            }
        }

        if (!empty($_FILES['images']['name'][0])) {
            $limit   = min(count($_FILES['images']['name']), 5);
            $indices = range(0, $limit - 1);
            $order   = array_values(array_unique(array_intersect($image_order, $indices)));
            foreach ($indices as $idx) {
                if (!in_array($idx, $order, true)) {
                    $order[] = $idx;
                }
            }

            foreach ($order as $i) {
                if (empty($_FILES['images']['name'][$i])) {
                    continue;
                }
                $_FILES['ap_image'] = [
                    'name'     => $_FILES['images']['name'][$i],
                    'type'     => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];

                $id = media_handle_upload('ap_image', $post_id);
                if (!is_wp_error($id)) {
                    $image_ids[] = $id;
                }
            }
            unset($_FILES['ap_image']);
        }

        if ($image_ids) {
            update_post_meta($post_id, '_ap_submission_images', $image_ids);
            update_post_meta($post_id, 'event_banner_id', $image_ids[0]);
            set_post_thumbnail($post_id, $image_ids[0]);
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
        \ArtPulse\Core\EmailService::send($admin_email, $subject, $message);

        // User confirmation
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_subject = 'Thanks for submitting your event';
        $user_message = "Hi {$current_user->display_name},\n\nThanks for submitting your event \"{$title}\". It is now pending review.";
        \ArtPulse\Core\EmailService::send($user_email, $user_subject, $user_message);

        if (function_exists('wc_add_notice')) {
            wc_add_notice('Event submitted successfully!', 'success');
        }
    }
}
