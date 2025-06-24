<?php

namespace ArtPulse\Frontend;

class OrganizationDashboardShortcode {
    public static function register() {
        add_shortcode('ap_org_dashboard', [self::class, 'render']);
        // Match the JS action in ap-org-dashboard.js
        add_action('wp_ajax_ap_add_org_event', [self::class, 'handle_ajax_add_event']);
        add_action('wp_ajax_ap_delete_org_event', [self::class, 'handle_ajax_delete_event']);
    }

    public static function render($atts) {
        if (!is_user_logged_in()) return '<p>You must be logged in to view this dashboard.</p>';

        $user_id = get_current_user_id();
        $org_id = get_user_meta($user_id, 'ap_organization_id', true);
        if (!$org_id) return '<p>No organization assigned.</p>';


        $status = isset($_GET['status']) ? sanitize_key($_GET['status']) : 'all';
        $paged  = max(1, intval($_GET['paged'] ?? 1));

        $query = new \WP_Query([
            'post_type'      => 'artpulse_event',
            'post_status'    => $status === 'all' ? ['publish','pending','draft'] : [$status],
            'meta_key'       => '_ap_event_organization',
            'meta_value'     => $org_id,
            'posts_per_page' => 10,
            'paged'          => $paged,
        ]);

        ob_start();
        ?>
        <div class="ap-org-dashboard">
            <nav class="dashboard-nav">
                <a href="#membership"><span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?></a>
                <a href="#billing"><span class="dashicons dashicons-money"></span><?php esc_html_e('Billing', 'artpulse'); ?></a>
                <a href="#events"><span class="dashicons dashicons-calendar"></span><?php esc_html_e('Events', 'artpulse'); ?></a>
                <a href="#analytics"><span class="dashicons dashicons-chart-bar"></span><?php esc_html_e('Analytics', 'artpulse'); ?></a>
                <a href="#profile"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e('Profile', 'artpulse'); ?></a>
            </nav>

            <h2 id="membership"><?php _e('Membership','artpulse'); ?></h2>
            <div id="ap-membership-info"></div>
            <div id="ap-membership-actions"></div>

            <h2 id="billing"><?php _e('Next Payment','artpulse'); ?></h2>
            <div id="ap-next-payment"></div>
            <h2 id="transactions"><?php _e('Recent Transactions','artpulse'); ?></h2>
            <div id="ap-transactions"></div>

            <h2 id="events"><?php _e('Organization Events','artpulse'); ?></h2>
            <button id="ap-add-event-btn" class="ap-form-button" type="button"><?php esc_html_e('Add New Event','artpulse'); ?></button>

            <div id="ap-org-modal" class="ap-org-modal" style="display:none">
                <button id="ap-modal-close" type="button" class="ap-form-button">Close</button>
                <div id="ap-status-message" class="ap-form-messages" role="status" aria-live="polite"></div>
                <form id="ap-org-event-form" class="ap-form-container">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ap_org_dashboard_nonce'); ?>">

                    <label class="ap-form-label" for="ap_event_title">Event Title</label>
                    <input class="ap-form-input" id="ap_event_title" type="text" name="ap_event_title" required>

                    <label class="ap-form-label" for="ap_event_date">Event Date</label>
                    <input class="ap-form-input" id="ap_event_date" type="date" name="ap_event_date" required>

                    <label class="ap-form-label" for="ap_event_start_date">Start Date</label>
                    <input class="ap-form-input" id="ap_event_start_date" type="date" name="ap_event_start_date">

                    <label class="ap-form-label" for="ap_event_end_date">End Date</label>
                    <input class="ap-form-input" id="ap_event_end_date" type="date" name="ap_event_end_date">

                    <label class="ap-form-label" for="ap_event_location">Location</label>
                    <input class="ap-form-input ap-google-autocomplete" id="ap_event_location" type="text" name="ap_event_location">

                    <label class="ap-form-label" for="ap_venue_name">Venue Name</label>
                    <input class="ap-form-input" id="ap_venue_name" type="text" name="ap_venue_name">

                    <label class="ap-form-label" for="ap_event_street_address">Street Address</label>
                    <input class="ap-form-input" id="ap_event_street_address" type="text" name="ap_event_street_address">

                    <label class="ap-form-label" for="ap_event_country">Country</label>
                    <input class="ap-form-input" id="ap_event_country" type="text" name="ap_event_country">

                    <label class="ap-form-label" for="ap_event_state">State/Province</label>
                    <input class="ap-form-input" id="ap_event_state" type="text" name="ap_event_state">

                    <label class="ap-form-label" for="ap_event_city">City</label>
                    <input class="ap-form-input" id="ap_event_city" type="text" name="ap_event_city">

                    <label class="ap-form-label" for="ap_event_postcode">Postcode</label>
                    <input class="ap-form-input" id="ap_event_postcode" type="text" name="ap_event_postcode">

                    <input type="hidden" name="address_components" id="ap_address_components">

                    <label class="ap-form-label" for="ap_event_organizer_name">Organizer Name</label>
                    <input class="ap-form-input" id="ap_event_organizer_name" type="text" name="ap_event_organizer_name">

                    <label class="ap-form-label" for="ap_event_organizer_email">Organizer Email</label>
                    <input class="ap-form-input" id="ap_event_organizer_email" type="email" name="ap_event_organizer_email">

                    <label class="ap-form-label" for="ap_event_type">Event Type</label>
                    <select class="ap-form-select" id="ap_event_type" name="ap_event_type">
                        <?php
                        $terms = get_terms('artpulse_event_type', ['hide_empty' => false]);
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>

                    <label class="ap-form-label">
                        <input class="ap-form-input" type="checkbox" name="ap_event_featured" value="1"> Request Featured
                    </label>

                    <input type="hidden" name="ap_event_organization" value="<?php echo esc_attr($org_id); ?>">
                    <button class="ap-form-button" type="submit">Submit</button>
                </form>
            </div>

            <form method="get" class="ap-event-filter" style="margin-bottom:1em;">
                <label>Status
                    <select name="status" onchange="this.form.submit()">
                        <option value="all" <?php selected($status, 'all'); ?>>All</option>
                        <option value="publish" <?php selected($status, 'publish'); ?>>Published</option>
                        <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                        <option value="draft" <?php selected($status, 'draft'); ?>>Draft</option>
                    </select>
                </label>
            </form>

            <ul id="ap-org-events" class="ap-org-events">
                <?php
                foreach ($query->posts as $event) {
                    $edit = get_edit_post_link($event->ID);
                    echo '<li>' . esc_html($event->post_title);
                    if ($edit) {
                        echo ' <a href="' . esc_url($edit) . '" class="ap-edit-event">Edit</a>';
                    }
                    echo ' <button class="ap-delete-event" data-id="' . $event->ID . '">Delete</button></li>';
                }
                ?>
            </ul>
            <?php
            $base = add_query_arg('paged', '%#%');
            if ($status !== 'all') {
                $base = add_query_arg('status', $status, $base);
            }
            echo paginate_links([
                'base'    => $base,
                'format'  => '',
                'current' => $paged,
                'total'   => $query->max_num_pages,
            ]);
            ?>

            <h2 id="analytics"><?php _e('Analytics','artpulse'); ?></h2>
            <div id="ap-org-analytics"></div>

            <h2 id="profile"><?php _e('Profile','artpulse'); ?></h2>
            <?php echo do_shortcode('[ap_org_profile_edit]'); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function handle_ajax_add_event() {
        check_ajax_referer('ap_org_dashboard_nonce', 'nonce');

        if (!current_user_can('create_artpulse_event')) {
            wp_send_json_error(['message' => 'Insufficient permissions.']);
        }

        $title            = sanitize_text_field($_POST['ap_event_title']);
        $date             = sanitize_text_field($_POST['ap_event_date']);
        $start_date       = sanitize_text_field($_POST['ap_event_start_date'] ?? '');
        $end_date         = sanitize_text_field($_POST['ap_event_end_date'] ?? '');
        $location         = sanitize_text_field($_POST['ap_event_location']);
        $venue_name       = sanitize_text_field($_POST['ap_venue_name'] ?? '');
        $street           = sanitize_text_field($_POST['ap_event_street_address'] ?? '');
        $country          = sanitize_text_field($_POST['ap_event_country'] ?? '');
        $state            = sanitize_text_field($_POST['ap_event_state'] ?? '');
        $city             = sanitize_text_field($_POST['ap_event_city'] ?? '');
        $postcode         = sanitize_text_field($_POST['ap_event_postcode'] ?? '');
        $address_components = sanitize_text_field($_POST['address_components'] ?? '');
        $organizer_name   = sanitize_text_field($_POST['ap_event_organizer_name'] ?? '');
        $organizer_email  = sanitize_email($_POST['ap_event_organizer_email'] ?? '');
        $event_type       = intval($_POST['ap_event_type'] ?? 0);
        $featured         = isset($_POST['ap_event_featured']) ? '1' : '0';
        $org_id           = intval($_POST['ap_event_organization']);

        if (empty($title)) {
            wp_send_json_error(['message' => 'Please enter an event title.']);
        }

        if (empty($date)) {
            wp_send_json_error(['message' => 'Please enter an event date.']);
        }

        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date)) {
            wp_send_json_error(['message' => 'Please enter a valid date in YYYY-MM-DD format.']);
        }

        if ($org_id <= 0) {
            wp_send_json_error(['message' => 'Please select an organization.']);
        }

        $user_org = intval(get_user_meta(get_current_user_id(), 'ap_organization_id', true));
        if (!$user_org || $user_org !== $org_id) {
            wp_send_json_error('Permission denied');
        }

        $event_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'artpulse_event',
            'post_status' => 'pending'
        ]);

        if (!$event_id) {
            wp_send_json_error(['message' => 'Failed to insert post']);
        }

        update_post_meta($event_id, '_ap_event_date', $date);
        update_post_meta($event_id, 'event_start_date', $start_date);
        update_post_meta($event_id, 'event_end_date', $end_date);
        update_post_meta($event_id, '_ap_event_location', $location);
        update_post_meta($event_id, 'venue_name', $venue_name);
        update_post_meta($event_id, 'event_street_address', $street);
        update_post_meta($event_id, 'event_country', $country);
        update_post_meta($event_id, 'event_state', $state);
        update_post_meta($event_id, 'event_city', $city);
        update_post_meta($event_id, 'event_postcode', $postcode);
        update_post_meta($event_id, 'address_components', $address_components);
        update_post_meta($event_id, 'event_organizer_name', $organizer_name);
        update_post_meta($event_id, 'event_organizer_email', $organizer_email);
        update_post_meta($event_id, '_ap_event_organization', $org_id);
        update_post_meta($event_id, 'event_featured', $featured);

        if ($event_type) {
            wp_set_post_terms($event_id, [$event_type], 'artpulse_event_type');
        }

        // Reload the event list
        ob_start();
        $events = get_posts([
            'post_type'   => 'artpulse_event',
            'post_status' => ['publish','pending','draft'],
            'meta_key'    => '_ap_event_organization',
            'meta_value'  => $org_id,
        ]);
        foreach ($events as $event) {
            $edit = get_edit_post_link($event->ID);
            echo '<li>' . esc_html($event->post_title);
            if ($edit) {
                echo ' <a href="' . esc_url($edit) . '" class="ap-edit-event">Edit</a>';
            }
            echo ' <button class="ap-delete-event" data-id="' . $event->ID . '">Delete</button></li>';
        }
        $html = ob_get_clean();

        wp_send_json_success(['updated_list_html' => $html]);
    }

    public static function handle_ajax_delete_event() {
        check_ajax_referer('ap_org_dashboard_nonce', 'nonce');

        if (!current_user_can('delete_post', intval($_POST['event_id'] ?? 0))) {
            wp_send_json_error(['message' => 'Insufficient permissions.']);
        }

        $event_id = intval($_POST['event_id'] ?? 0);
        $post     = get_post($event_id);

        if (!$post || get_post_type($event_id) !== 'artpulse_event') {
            wp_send_json_error(['message' => 'Invalid event.']);
        }

        $user_id    = get_current_user_id();
        $user_org   = get_user_meta($user_id, 'ap_organization_id', true);
        $event_org  = intval(get_post_meta($event_id, '_ap_event_organization', true));

        if (!$user_org || $user_org != $event_org) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        wp_delete_post($event_id, true);

        // Reload the event list for this organization
        ob_start();
        $events = get_posts([
            'post_type'   => 'artpulse_event',
            'post_status' => ['publish','pending','draft'],
            'meta_key'    => '_ap_event_organization',
            'meta_value'  => $user_org,
        ]);
        foreach ($events as $event) {
            $edit = get_edit_post_link($event->ID);
            echo '<li>' . esc_html($event->post_title);
            if ($edit) {
                echo ' <a href="' . esc_url($edit) . '" class="ap-edit-event">Edit</a>';
            }
            echo ' <button class="ap-delete-event" data-id="' . $event->ID . '">Delete</button></li>';
        }
        $html = ob_get_clean();

        wp_send_json_success(['updated_list_html' => $html]);
    }
}
