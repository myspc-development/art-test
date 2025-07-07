<?php

namespace ArtPulse\Frontend;

class EventSubmissionShortcode {
    /**
     * Transient key used when WooCommerce is unavailable.
     */
    private const NOTICE_KEY = 'ap_event_notices';

    /**
     * Store a notice for later display.
     */
    protected static function add_notice(string $message, string $type = 'error'): void {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($message, $type);
            return;
        }
        $notices   = get_transient(self::NOTICE_KEY) ?: [];
        $notices[] = [ 'message' => $message, 'type' => $type ];
        set_transient(self::NOTICE_KEY, $notices, defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60);
    }

    /**
     * Output any stored notices.
     */
    protected static function print_notices(): void {
        if (function_exists('wc_print_notices')) {
            wc_print_notices();
            return;
        }
        $notices = get_transient(self::NOTICE_KEY);
        if ($notices) {
            foreach ($notices as $notice) {
                $type    = esc_attr($notice['type']);
                $message = esc_html($notice['message']);
                echo "<div class='notice {$type}'>{$message}</div>";
            }
            delete_transient(self::NOTICE_KEY);
        }
    }

    /**
     * Redirect back to the form when possible.
     */
    protected static function maybe_redirect(): void {
        if (function_exists('wp_safe_redirect') && function_exists('wp_get_referer')) {
            $target = wp_get_referer();
            if (!$target) {
                $target = \ArtPulse\Core\Plugin::get_event_submission_url();
            }
            wp_safe_redirect($target);
            exit;
        }
    }

    public static function register() {
        add_shortcode('ap_submit_event', [self::class, 'render']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_scripts']); // Enqueue scripts and styles
        // Use a later priority so the handler runs during the same request
        // even though this callback is added while the `init` action is firing.
        add_action('init', [self::class, 'maybe_handle_form'], 20); // Handle form submission
    }

    public static function enqueue_scripts() {
        // Ensure the core UI styles are loaded
        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }
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

        $artists = get_posts([
            'post_type'   => 'artpulse_artist',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);

        ob_start();
        ?>
        <div class="ap-form-messages" role="status" aria-live="polite">
            <?php self::print_notices(); ?>
        </div>

        <form method="post" enctype="multipart/form-data" class="ap-form-container" data-no-ajax="true">
            <?php wp_nonce_field('ap_submit_event', 'ap_event_nonce'); ?>
            <input type="hidden" name="ap_submit_event" value="1" />

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
                <label class="ap-form-label" for="ap_event_start_time">Start Time</label>
                <input class="ap-input" id="ap_event_start_time" type="time" name="event_start_time" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_end_time">End Time</label>
                <input class="ap-input" id="ap_event_end_time" type="time" name="event_end_time" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_recurrence">Recurrence Rule (iCal)</label>
                <input class="ap-input" id="ap_event_recurrence" type="text" name="event_recurrence_rule" />
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
                <label class="ap-form-label" for="ap_event_address">Address</label>
                <input class="ap-input" id="ap_event_address" type="text" name="event_address" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_contact">Contact Info</label>
                <input class="ap-input" id="ap_event_contact" type="text" name="event_contact" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_rsvp">RSVP URL</label>
                <input class="ap-input" id="ap_event_rsvp" type="url" name="event_rsvp_url" />
            </p>

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
                    $terms = get_terms(['taxonomy' => 'event_type', 'hide_empty' => false]);
                    foreach ($terms as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
            </p>

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
                <label class="ap-form-label" for="ap_event_artists">Co-Host Artists</label>
                <select class="ap-input" id="ap_event_artists" name="event_artists[]" multiple>
                    <?php foreach ($artists as $artist): ?>
                        <option value="<?= esc_attr($artist->ID) ?>"><?= esc_html($artist->post_title) ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_banner">Event Banner</label>
                <input class="ap-input" id="ap_event_banner" type="file" name="event_banner" />
            </p>

            <p>
                <label class="ap-form-label" for="ap_event_images">Additional Images (max 5)</label>
                <input class="ap-input" id="ap_event_images" type="file" name="images[]" multiple />
            </p>

            <p>
                <label class="ap-form-label">
                    <input class="ap-input" type="checkbox" name="event_featured" value="1" /> Request Featured
                </label>
            </p>

            <p>
                <label><input type="radio" name="event_status" value="publish" checked> Publish Now</label>
                <label><input type="radio" name="event_status" value="draft"> Save as Draft</label>
                <label><input type="radio" name="event_status" value="future"> Schedule</label>
                <input type="datetime-local" name="event_publish_date" value="">
            </p>

            <p>
                <button class="ap-form-button nectar-button" type="submit" name="ap_submit_event">Submit Event</button>
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
            self::add_notice('Security check failed.', 'error');
            self::maybe_redirect();
            return;
        }

        $user_id = get_current_user_id();

        // Validate event data
        $event_title = sanitize_text_field($_POST['event_title']);
        $event_description = wp_kses_post($_POST['event_description']);
        $event_date = sanitize_text_field($_POST['event_date']);
        $start_date = sanitize_text_field($_POST['event_start_date'] ?? '');
        $end_date   = sanitize_text_field($_POST['event_end_date'] ?? '');
        $recurrence  = sanitize_text_field($_POST['event_recurrence_rule'] ?? '');
        $event_location = sanitize_text_field($_POST['event_location']);
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
        $event_org = intval($_POST['event_org']);
        $event_artists = isset($_POST['event_artists']) ? array_map('intval', (array) $_POST['event_artists']) : [];
        $event_type = intval($_POST['event_type'] ?? 0);
        $featured = isset($_POST['event_featured']) ? '1' : '0';

        if (empty($event_title)) {
            self::add_notice('Please enter an event title.', 'error');
            self::maybe_redirect();
            return; // Stop processing
        }

        if (empty($event_description)) {
            self::add_notice('Please enter an event description.', 'error');
            self::maybe_redirect();
            return;
        }

        if (empty($event_date)) {
            self::add_notice('Please enter an event date.', 'error');
            self::maybe_redirect();
            return;
        }

        // Validate the date format
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $event_date)) {
            self::add_notice('Please enter a valid date in YYYY-MM-DD format.', 'error');
            self::maybe_redirect();
            return;
        }

        // Validate that start date is not later than end date when both provided
        if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
            self::add_notice('Start date cannot be later than end date.', 'error');
            self::maybe_redirect();
            return;
        }

        if ($event_org <= 0) {
            self::add_notice('Please select an organization.', 'error');
            self::maybe_redirect();
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
            self::add_notice('Invalid organization selected.', 'error');
            self::maybe_redirect();
            return;
        }

        $status_choice = sanitize_text_field($_POST['event_status'] ?? 'publish');
        $publish_date  = sanitize_text_field($_POST['event_publish_date'] ?? '');
        $post_status   = 'publish';
        $post_date     = null;

        if ($status_choice === 'draft') {
            $post_status = 'draft';
        } elseif ($status_choice === 'future') {
            $post_status = 'future';
            if ($publish_date) {
                $post_date = $publish_date;
            } else {
                self::add_notice('Please provide a publish date.', 'error');
                self::maybe_redirect();
                return;
            }
        }

        $post_args = [
            'post_type'   => 'artpulse_event',
            'post_status' => $post_status,
            'post_title'  => $event_title,
            'post_content'=> $event_description,
            'post_author' => $user_id,
        ];

        if ($post_date) {
            $post_args['post_date'] = $publish_date;
        }

        $post_id = wp_insert_post($post_args);

        if (is_wp_error($post_id)) {
            error_log('Error creating event post: ' . $post_id->get_error_message());
            self::add_notice('Error submitting event. Please try again later.', 'error');
            self::maybe_redirect();
            return;
        }

        update_post_meta($post_id, '_ap_event_date', $event_date);
        update_post_meta($post_id, 'event_start_date', $start_date);
        update_post_meta($post_id, 'event_end_date', $end_date);
        update_post_meta($post_id, 'event_recurrence_rule', $recurrence);
        update_post_meta($post_id, '_ap_event_location', $event_location);
        update_post_meta($post_id, 'venue_name', $venue_name);
        update_post_meta($post_id, 'event_street_address', $street);
        update_post_meta($post_id, 'event_country', $country);
        update_post_meta($post_id, 'event_state', $state);
        update_post_meta($post_id, 'event_city', $city);
        update_post_meta($post_id, 'event_postcode', $postcode);
        if (is_array($address_components)) {
            update_post_meta($post_id, 'address_components', wp_json_encode($address_components));
        } else {
            update_post_meta($post_id, 'address_components', $address_json);
        }
        update_post_meta($post_id, '_ap_event_address', $address_full);
        update_post_meta($post_id, '_ap_event_start_time', $start_time);
        update_post_meta($post_id, '_ap_event_end_time', $end_time);
        update_post_meta($post_id, '_ap_event_contact', $contact_info);
        update_post_meta($post_id, '_ap_event_rsvp', $rsvp_url);
        update_post_meta($post_id, 'event_organizer_name', $organizer_name);
        update_post_meta($post_id, 'event_organizer_email', $organizer_email);
        update_post_meta($post_id, '_ap_event_organization', $event_org);
        update_post_meta($post_id, '_ap_event_artists', $event_artists);
        update_post_meta($post_id, 'event_featured', $featured);

        if ($event_type) {
            wp_set_post_terms($post_id, [$event_type], 'event_type');
        }

        // Handle banner and additional image uploads
        $image_ids = [];
        $image_order = [];

        if (isset($_POST['image_order'])) {
            $image_order = array_map('intval', array_filter(explode(',', (string) $_POST['image_order'])));
        }

        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Handle Banner Upload
        if (!empty($_FILES['event_banner']['name'])) {
            $attachment_id = media_handle_upload('event_banner', $post_id);

            if (!is_wp_error($attachment_id)) {
                $image_ids[] = $attachment_id;
                // Set the featured image
                set_post_thumbnail($post_id, $attachment_id);
                update_post_meta($post_id, 'event_banner_id', $attachment_id); // Store banner ID separately
            } else {
                error_log('Error uploading banner: ' . $attachment_id->get_error_message());
                self::add_notice('Error uploading banner. Please try again.', 'error');
                self::maybe_redirect();
                return;
            }
        }

        // Handle Additional Images Upload
        if (!empty($_FILES['images']['name'][0])) {
            $files = $_FILES['images'];
            $limit = min(count($files['name']), 5); // Limit to 5 images

            for ($i = 0; $i < $limit; $i++) {
                if (!empty($files['name'][$i])) {
                    $file = array(
                        'name'     => $files['name'][$i],
                        'type'     => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error'    => $files['error'][$i],
                        'size'     => $files['size'][$i]
                    );

                    $_FILES['ap_image'] = $file; // Rename to avoid conflicts

                    $attachment_id = media_handle_upload('ap_image', $post_id);

                    if (!is_wp_error($attachment_id)) {
                        $image_ids[] = $attachment_id;
                    } else {
                        error_log('Error uploading additional image: ' . $attachment_id->get_error_message());
                        self::add_notice('Error uploading additional image. Please try again.', 'error');
                        self::maybe_redirect();
                        return;
                    }
                }
            }
            unset($_FILES['ap_image']); // Clean up
        }

        // Handle Image Order (reordering logic)
        $final_image_ids = [];

        if (!empty($image_order)) {
            // Reorder images based on user-defined order
            foreach ($image_order as $image_id) {
                if (in_array($image_id, $image_ids)) {
                    $final_image_ids[] = $image_id;
                }
            }

            // Add any remaining images that weren't in the order (append them)
            foreach ($image_ids as $image_id) {
                if (!in_array($image_id, $final_image_ids)) {
                    $final_image_ids[] = $image_id;
                }
            }
        } else {
            // No order specified, use the order images were uploaded
            $final_image_ids = $image_ids;
        }

        // Update Post Meta with Image IDs (including banner when present)
        update_post_meta($post_id, '_ap_submission_images', $final_image_ids);



        // Success message and redirect
        self::add_notice('Event submitted successfully! It is awaiting review.', 'success');
        self::maybe_redirect();
    }
}