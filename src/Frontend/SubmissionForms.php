<?php

namespace ArtPulse\Frontend;

/**
 * Handles output of the front-end submission form and wiring up JS validation.
 */
class SubmissionForms
{
    /**
     * Register shortcode for submission form.
     */
    public static function register(): void
    {
        add_shortcode('ap_submission_form', [__CLASS__, 'render_form']);
    }

    /**
     * Render the submission form HTML.
     *
     * Usage: [ap_submission_form post_type="artpulse_event"]
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML form.
     */
    public static function render_form(array $atts): string
    {
        $atts = shortcode_atts(
            [
                'post_type' => 'artpulse_event',
            ],
            $atts,
            'ap_submission_form'
        );

        // Form classes and data
        $post_type = esc_attr($atts['post_type']);

        ob_start();
        ?>
        <form class="ap-submission-form ap-form-container" data-post-type="<?php echo $post_type; ?>">
            <p>
                <label class="ap-form-label" for="ap-title"><?php esc_html_e('Title*', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-title" type="text" name="title" data-required="<?php esc_attr_e('Title is required', 'artpulse'); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-date"><?php esc_html_e('Date*', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-date" type="date" name="event_date" data-required="<?php esc_attr_e('Date is required', 'artpulse'); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-start-date"><?php esc_html_e('Start Date*', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-start-date" type="date" name="event_start_date" data-required="<?php esc_attr_e('Start date is required', 'artpulse'); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-end-date"><?php esc_html_e('End Date', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-end-date" type="date" name="event_end_date" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-venue-name"><?php esc_html_e('Venue Name', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-venue-name" type="text" name="venue_name" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-street"><?php esc_html_e('Street Address', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-street" type="text" name="event_street_address" class="ap-address-street ap-address-input" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-country"><?php esc_html_e('Country*', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-country" type="text" name="event_country" class="ap-address-country ap-address-input" data-required="<?php esc_attr_e('Country is required', 'artpulse'); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-state"><?php esc_html_e('State/Province', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-state" type="text" name="event_state" class="ap-address-state ap-address-input" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-city"><?php esc_html_e('City', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-city" type="text" name="event_city" class="ap-address-city ap-address-input" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-postcode"><?php esc_html_e('Postcode', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-postcode" type="text" name="event_postcode" class="ap-address-postcode ap-address-input" />
            </p>
            <input class="ap-form-input" id="ap-location" type="hidden" name="event_location" />
            <input class="ap-form-input" type="hidden" name="address_components" id="ap-address-components" />
            <p>
                <label class="ap-form-label" for="ap-organizer-name"><?php esc_html_e('Organizer Name', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-organizer-name" type="text" name="event_organizer_name" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-organizer-email"><?php esc_html_e('Organizer Email', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-organizer-email" type="email" name="event_organizer_email" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-banner"><?php esc_html_e('Event Banner', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-banner" type="file" accept="image/*" />
            </p>
            <p>
                <label>
                    <input class="ap-form-input" id="ap-featured" type="checkbox" name="event_featured" value="1" />
                    <?php esc_html_e('Request Featured', 'artpulse'); ?>
                </label>
            </p>
            <p>
                <label class="ap-form-label" for="ap-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-images" type="file" name="images[]" accept="image/*" multiple />
            </p>
            <p>
                <button class="ap-form-button" type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
            </p>
        </form>
        <ul class="ap-submissions-list"></ul>
        <?php
        return ob_get_clean();
    }
}
