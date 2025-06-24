<?php

namespace ArtPulse\Frontend;

class EventSubmissionShortcode {

    public static function register() {
        add_shortcode('ap_submit_event', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']); // Enqueue scripts and styles
        add_action('init', [self::class, 'maybe_handle_form']); // Handle form submission
    }

    public static function enqueue_scripts() {
        // Use the common form styles shared across the plugin
        wp_enqueue_style('ap-forms-css');
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to submit an event.</p>';
        }

        $user_id = get_current_user_id();

        $orgs = get_posts([
            'post_type'   => 'artpulse_org',
            'author'      => $user_id,
            'numberposts' => -1,
        ]);

        ob_start();
        ?>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <form method="post" enctype="multipart/form-data" class="ap-event-form ap-form-container">
            <?php wp_nonce_field('ap_submit_event', 'ap_event_nonce'); ?>

            <p>
                <label class="ap-form-label" for="ap_event_title">Event Title</label>
                <input class="ap-input" id="ap_event_title" type="text" name="event_title" required />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_description">Description</label>
                <textarea class="ap-input" id="ap_event_description" name="event_description" rows="5" required></textarea>
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_date">Date</label>
                <input class="ap-input" id="ap_event_date" type="date" name="event_date" required />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_start_date">Start Date</label>
                <input class="ap-input" id="ap_event_start_date" type="date" name="event_start_date" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_end_date">End Date</label>
                <input class="ap-input" id="ap_event_end_date" type="date" name="event_end_date" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_location">Location</label>
                <input class="ap-input ap-google-autocomplete" id="ap_event_location" type="text" name="event_location" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_venue_name">Venue Name</label>
                <input class="ap-input" id="ap_venue_name" type="text" name="venue_name" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_street_address">Street Address</label>
                <input class="ap-input" id="ap_event_street_address" type="text" name="event_street_address" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_country">Country</label>
                <input class="ap-input" id="ap_event_country" type="text" name="event_country" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_state">State/Province</label>
                <input class="ap-input" id="ap_event_state" type="text" name="event_state" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_city">City</label>
                <input class="ap-input" id="ap_event_city" type="text" name="event_city" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_postcode">Postcode</label>
                <input class="ap-input" id="ap_event_postcode" type="text" name="event_postcode" />
            </p>

            <input type="hidden" name="address_components" id="ap_address_components" />

            <p>
                <label class="ap-form-label" for="ap_event_organizer_name">Organizer Name</label>
                <input class="ap-input" id="ap_event_organizer_name" type="text" name="event_organizer_name" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_organizer_email">Organizer Email</label>
                <input class="ap-input" id="ap_event_organizer_email" type="email" name="event_organizer_email" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_type">Event Type</label>
                <select class="ap-input" id="ap_event_type" name="event_type">
                <option value="">Select Type</option>
                <?php
                $terms = get_terms(['taxonomy' => 'artpulse_event_type', 'hide_empty' => false]);
                foreach ($terms as $term) {
                    echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                }
                ?>
            </select>

            <p>
                <label class="ap-form-label" for="ap_event_org">Organization</label>
                <select class="ap-input" id="ap_event_org" name="event_org" required>
                    <option value="">Select Organization</option>
                    <?php foreach ($orgs as $org): ?>
                        <option value="<?= esc_attr($org->ID) ?>"><?= esc_html($org->post_title) ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_banner">Event Banner</label>
                <input class="ap-input" id="ap_event_banner" type="file" name="event_banner" />
            </p>

            <p>
                <label class="ap-form-label">
                    <input class="ap-input" type="checkbox" name="event_featured" value="1" /> Request Featured
                </label>
            </p>

            <p>
                <button class="ap-form-button" type="submit" name="ap_submit_event">Submit Event</button>
            </p>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function maybe_handle_form() {
        if (!is_user_logged_in() || !isset($_POST['ap_submit_event'])) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['ap_event_nonce']) || !wp_verify_nonce($_POST['ap_event_nonce'], 'ap_submit_event')) {
            wp_die('Security check failed.'); // Or redirect with an error message
            return;
        }

        $user_id = get_current_user_id();

        // Validate event data
        $event_title = sanitize_text_field($_POST['event_title']);
        $event_description = wp_kses_post($_POST['event_description']);
        $event_date = sanitize_text_field($_POST['event_date']);
        $start_date = sanitize_text_field($_POST['event_start_date'] ?? '');
        $end_date   = sanitize_text_field($_POST['event_end_date'] ?? '');
        $event_location = sanitize_text_field($_POST['event_location']);
        $venue_name = sanitize_text_field($_POST['venue_name'] ?? '');
        $street = sanitize_text_field($_POST['event_street_address'] ?? '');
        $country = sanitize_text_field($_POST['event_country'] ?? '');
        $state = sanitize_text_field($_POST['event_state'] ?? '');
        $city = sanitize_text_field($_POST['event_city'] ?? '');
        $postcode = sanitize_text_field($_POST['event_postcode'] ?? '');
        $address_components = sanitize_text_field($_POST['address_components'] ?? '');
        $organizer_name = sanitize_text_field($_POST['event_organizer_name'] ?? '');
        $organizer_email = sanitize_email($_POST['event_organizer_email'] ?? '');
        $event_org = intval($_POST['event_org']);
        $event_type = intval($_POST['event_type'] ?? 0);
        $featured = isset($_POST['event_featured']) ? '1' : '0';

        if (empty($event_title)) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Please enter an event title.', 'error'); // Or use your notification system
            } else {
                wp_die('Please enter an event title.');
            }
            return; // Stop processing
        }

        if (empty($event_description)) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Please enter an event description.', 'error');
            } else {
                wp_die('Please enter an event description.');
            }
            return;
        }

        if (empty($event_date)) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Please enter an event date.', 'error');
            } else {
                wp_die('Please enter an event date.');
            }
            return;
        }
          // Validate the date format
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $event_date)) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Please enter a valid date in YYYY-MM-DD format.', 'error');
            } else {
                wp_die('Please enter a valid date in YYYY-MM-DD format.');
            }
            return;
        }

        if ($event_org <= 0) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Please select an organization.', 'error');
            } else {
                wp_die('Please select an organization.');
            }
            return;
        }

        // Verify the organization belongs to the current user
        $user_orgs = get_posts([
            'post_type'   => 'artpulse_org',
            'author'      => $user_id,
            'numberposts' => -1,
        ]);
        $authorized = array_map('intval', wp_list_pluck($user_orgs, 'ID'));
        $meta_org = intval(get_user_meta($user_id, 'ap_organization_id', true));
        if ($meta_org) {
            $authorized[] = $meta_org;
        }
        if (!in_array($event_org, $authorized, true)) {
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Invalid organization selected.', 'error');
            } else {
                wp_die('Invalid organization selected.');
            }
            return;
        }

        $post_id = wp_insert_post([
            'post_type'   => 'artpulse_event',
            'post_status' => 'pending',
            'post_title'  => $event_title,
            'post_content'=> $event_description,
            'post_author' => $user_id,
        ]);

        if (is_wp_error($post_id)) {
            error_log('Error creating event post: ' . $post_id->get_error_message());
            if (function_exists('wc_add_notice')) {
                wc_add_notice('Error submitting event. Please try again later.', 'error');
            } else {
                wp_die('Error submitting event. Please try again later.');
            }
            return;
        }

        update_post_meta($post_id, '_ap_event_date', $event_date);
        update_post_meta($post_id, 'event_start_date', $start_date);
        update_post_meta($post_id, 'event_end_date', $end_date);
        update_post_meta($post_id, '_ap_event_location', $event_location);
        update_post_meta($post_id, 'venue_name', $venue_name);
        update_post_meta($post_id, 'event_street_address', $street);
        update_post_meta($post_id, 'event_country', $country);
        update_post_meta($post_id, 'event_state', $state);
        update_post_meta($post_id, 'event_city', $city);
        update_post_meta($post_id, 'event_postcode', $postcode);
        update_post_meta($post_id, 'address_components', $address_components);
        update_post_meta($post_id, 'event_organizer_name', $organizer_name);
        update_post_meta($post_id, 'event_organizer_email', $organizer_email);
        update_post_meta($post_id, '_ap_event_organization', $event_org);
        update_post_meta($post_id, 'event_featured', $featured);

        if ($event_type) {
            wp_set_post_terms($post_id, [$event_type], 'artpulse_event_type');
        }
        // Handle banner upload
        if (!empty($_FILES['event_banner']['name'])) {
            if ( ! function_exists( 'media_handle_upload' ) ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
            }

            $attachment_id = media_handle_upload('event_banner', $post_id);

            if (is_wp_error($attachment_id)) {
                error_log('Error uploading image: ' . $attachment_id->get_error_message());
                if (function_exists('wc_add_notice')) {
                    wc_add_notice('Error uploading banner. Please try again.', 'error');
                } else {
                    wp_die('Error uploading banner. Please try again.');
                }
            } else {
                update_post_meta($post_id, 'event_banner_id', $attachment_id);
            }
        }

        // Success message and redirect
        if (function_exists('wc_add_notice')) {
            wc_add_notice('Event submitted successfully! It is awaiting review.', 'success');
        } else {
            wp_die('Event submitted successfully! It is awaiting review.');
        }
    }
}