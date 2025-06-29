<?php

namespace ArtPulse\Frontend;

class OrganizationDashboardShortcode {
    public static function register() {
        add_shortcode('ap_org_dashboard', [self::class, 'render']);
        // Match the JS action in ap-org-dashboard.js
        add_action('wp_ajax_ap_add_org_event', [self::class, 'handle_ajax_add_event']);
        add_action('wp_ajax_ap_get_org_event', [self::class, 'handle_ajax_get_event']);
        add_action('wp_ajax_ap_update_org_event', [self::class, 'handle_ajax_update_event']);
        add_action('wp_ajax_ap_delete_org_event', [self::class, 'handle_ajax_delete_event']);
    }

    /**
     * Return artwork posts grouped by project stage for an organization.
     */
    public static function get_project_stage_groups(int $org_id): array {
        $groups = [];

        $terms = get_terms([
            'taxonomy'   => 'ap_project_stage',
            'hide_empty' => false,
        ]);
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $groups[$term->slug] = [
                    'slug'  => $term->slug,
                    'name'  => $term->name,
                    'items' => [],
                ];
            }
        }

        $posts = get_posts([
            'post_type'      => 'artpulse_artwork',
            'post_status'    => ['publish','pending','draft'],
            'numberposts'    => -1,
            'meta_key'       => 'org_id',
            'meta_value'     => $org_id,
        ]);

        foreach ($posts as $p) {
            $stage_terms = get_the_terms($p->ID, 'ap_project_stage');
            $slug = '';
            $name = '';
            if ($stage_terms && !is_wp_error($stage_terms)) {
                $slug = $stage_terms[0]->slug;
                $name = $stage_terms[0]->name;
            }

            if (!isset($groups[$slug])) {
                $groups[$slug] = [
                    'slug'  => $slug,
                    'name'  => $name ?: __('Uncategorized', 'artpulse'),
                    'items' => [],
                ];
            }

            $groups[$slug]['items'][] = [
                'id'    => $p->ID,
                'title' => $p->post_title,
            ];
        }

        return array_values($groups);
    }

    public static function render($atts) {
        if (!is_user_logged_in()) return '<p>You must be logged in to view this dashboard.</p>';

        // Capability checks for advanced dashboard features
        $can_manage       = current_user_can('manage_options');
        $can_edit_others  = current_user_can('edit_others_posts');

        $user_id = get_current_user_id();
        $org_id = get_user_meta($user_id, 'ap_organization_id', true);
        if (!$org_id) return '<p>No organization assigned.</p>';

        $show_analytics = $can_manage || $can_edit_others;

        $stage_groups = self::get_project_stage_groups($org_id);


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
        <div class="ap-dashboard">
            <h1><?php esc_html_e('Organization Dashboard','artpulse'); ?></h1>
            <nav class="dashboard-nav">
                <a href="#membership"><span class="dashicons dashicons-admin-users"></span><?php esc_html_e('Membership', 'artpulse'); ?></a>
                <a href="#billing"><span class="dashicons dashicons-money"></span><?php esc_html_e('Billing', 'artpulse'); ?></a>
                <a href="#events"><span class="dashicons dashicons-calendar"></span><?php esc_html_e('Events', 'artpulse'); ?></a>
                <?php if ($show_analytics) : ?>
                <a href="#analytics"><span class="dashicons dashicons-chart-bar"></span><?php esc_html_e('Analytics', 'artpulse'); ?></a>
                <?php endif; ?>
                <a href="#profile"><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e('Profile', 'artpulse'); ?></a>
            </nav>

            <details class="ap-widget" id="membership-section" open>
                <summary><h2 id="membership"><?php _e('Membership','artpulse'); ?></h2></summary>
                <div id="ap-membership-info"></div>
                <div id="ap-membership-actions"></div>
            </details>

            <details class="ap-widget" id="billing-section" open>
                <summary><h2 id="billing"><?php _e('Next Payment','artpulse'); ?></h2></summary>
                <div id="ap-next-payment"></div>
                <h3 id="transactions"><?php _e('Recent Transactions','artpulse'); ?></h3>
                <div id="ap-transactions"></div>
            </details>

            <details class="ap-widget" id="events-section" open>
                <summary><h2 id="events"><?php _e('Organization Events','artpulse'); ?></h2></summary>
                <?php if (current_user_can('edit_posts')) : ?>
                <button id="ap-add-event-btn" class="ap-form-button nectar-button" type="button"><?php esc_html_e('Add New Event','artpulse'); ?></button>
                <?php endif; ?>
                <p class="ap-help-text">
                    <?php printf(
                        __('Use the <a href="%s">Event Submission</a> page to submit a new event for admin approval.', 'artpulse'),
                        esc_url(\ArtPulse\Core\Plugin::get_event_submission_url())
                    ); ?>
                </p>

                <div id="ap-org-modal" class="ap-org-modal container">
                    <button id="ap-modal-close" type="button" class="ap-form-button nectar-button">Close</button>
                    <div id="ap-status-message" class="ap-form-messages" role="status" aria-live="polite"></div>
                <form id="ap-org-event-form" class="ap-form-container">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ap_org_dashboard_nonce'); ?>">

                    <label class="ap-form-label" for="ap_event_title">Event Title</label>
                    <input class="ap-input" id="ap_event_title" type="text" name="ap_event_title" required>

                    <label class="ap-form-label" for="ap_event_date">Event Date</label>
                    <input class="ap-input" id="ap_event_date" type="date" name="ap_event_date" required>

                    <label class="ap-form-label" for="ap_event_start_date">Start Date</label>
                    <input class="ap-input" id="ap_event_start_date" type="date" name="ap_event_start_date">

                    <label class="ap-form-label" for="ap_event_end_date">End Date</label>
                    <input class="ap-input" id="ap_event_end_date" type="date" name="ap_event_end_date">

                    <label class="ap-form-label" for="ap_event_location">Location</label>
                    <input class="ap-input ap-google-autocomplete" id="ap_event_location" type="text" name="ap_event_location">

                    <label class="ap-form-label" for="ap_venue_name">Venue Name</label>
                    <input class="ap-input" id="ap_venue_name" type="text" name="ap_venue_name">

                    <label class="ap-form-label" for="ap_event_street_address">Street Address</label>
                    <input class="ap-input" id="ap_event_street_address" type="text" name="ap_event_street_address">

                    <label class="ap-form-label" for="ap_event_country">Country</label>
                    <input class="ap-input" id="ap_event_country" type="text" name="ap_event_country">

                    <label class="ap-form-label" for="ap_event_state">State/Province</label>
                    <input class="ap-input" id="ap_event_state" type="text" name="ap_event_state">

                    <label class="ap-form-label" for="ap_event_city">City</label>
                    <input class="ap-input" id="ap_event_city" type="text" name="ap_event_city">

                    <label class="ap-form-label" for="ap_event_postcode">Postcode</label>
                    <input class="ap-input" id="ap_event_postcode" type="text" name="ap_event_postcode">

                    <input type="hidden" name="address_components" id="ap_address_components">

                    <label class="ap-form-label" for="ap_event_organizer_name">Organizer Name</label>
                    <input class="ap-input" id="ap_event_organizer_name" type="text" name="ap_event_organizer_name">

                    <label class="ap-form-label" for="ap_event_organizer_email">Organizer Email</label>
                    <input class="ap-input" id="ap_event_organizer_email" type="email" name="ap_event_organizer_email">

                    <label class="ap-form-label" for="ap_event_type">Event Type</label>
                    <select class="ap-input" id="ap_event_type" name="ap_event_type">
                        <?php
                        $terms = get_terms('artpulse_event_type', ['hide_empty' => false]);
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>

                    <label class="ap-form-label">
                        <input class="ap-input" type="checkbox" name="ap_event_featured" value="1"> Request Featured
                    </label>

                    <input type="hidden" name="ap_event_organization" value="<?php echo esc_attr($org_id); ?>">
                    <input type="hidden" name="ap_event_id" id="ap_event_id" value="">
                    <button class="ap-form-button nectar-button" type="submit">Submit</button>
                </form>
            </div>

            <form method="get" class="ap-event-filter">
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
                    $rsvps = get_post_meta($event->ID, 'event_rsvp_list', true);
                    $wait  = get_post_meta($event->ID, 'event_waitlist', true);
                    $limit = intval(get_post_meta($event->ID, 'event_rsvp_limit', true));
                    $rsvp_count = is_array($rsvps) ? count($rsvps) : 0;
                    $wait_count = is_array($wait) ? count($wait) : 0;
                    echo '<li data-event="' . $event->ID . '">' . esc_html($event->post_title);
                    echo ' <span class="ap-rsvp-count">(' . $rsvp_count . '/' . ($limit ?: '&infin;') . ')</span>';
                    if ($wait_count) {
                        echo ' <span class="ap-waitlist-count">' . intval($wait_count) . ' WL</span>';
                    }
                    echo ' <a href="#" class="ap-view-attendees" data-id="' . $event->ID . '">Attendees</a>';
                    echo ' <a href="#" class="ap-inline-edit" data-id="' . $event->ID . '">Edit</a>';
                    echo ' <button class="ap-config-rsvp" data-id="' . $event->ID . '">Configure RSVP</button>';
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
            </details>

            <div id="ap-attendee-modal" class="ap-org-modal container">
                <button id="ap-attendee-close" type="button" class="ap-form-button nectar-button">Close</button>
                <button id="ap-attendee-export" type="button" class="ap-form-button nectar-button">Export CSV</button>
                <button id="ap-attendee-message-all" type="button" class="ap-form-button nectar-button">Message All</button>
                <div id="ap-attendee-content"></div>
            </div>

            <div id="ap-message-modal" class="ap-org-modal container">
                <button id="ap-message-close" type="button" class="ap-form-button nectar-button">Close</button>
                <form id="ap-message-form" class="ap-form-container">
                    <label class="ap-form-label" for="ap-message-subject">Subject</label>
                    <input id="ap-message-subject" class="ap-input" type="text" required>
                    <label class="ap-form-label" for="ap-message-body">Message</label>
                    <textarea id="ap-message-body" class="ap-input" required></textarea>
                    <button class="ap-form-button nectar-button" type="submit">Send</button>
                </form>
            </div>

            <div id="kanban-board"></div>

            <?php if ($show_analytics) : ?>
            <details class="ap-widget" id="analytics-section" open>
                <summary><h2 id="analytics"><?php _e('Analytics','artpulse'); ?></h2></summary>
                <div id="ap-org-analytics"></div>
            </details>
            <?php endif; ?>

            <details class="ap-widget" id="profile-section" open>
                <summary><h2 id="profile"><?php _e('Profile','artpulse'); ?></h2></summary>
                <?php
                $days  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                $hours = [];
                foreach ($days as $day) {
                    $start  = get_post_meta($org_id, "ead_org_{$day}_start_time", true);
                    $end    = get_post_meta($org_id, "ead_org_{$day}_end_time", true);
                    $closed = get_post_meta($org_id, "ead_org_{$day}_closed", true);
                    if ($start || $end || $closed) {
                        $hours[$day] = [$start, $end, $closed];
                    }
                }
                if ($hours) {
                    echo '<div class="ap-opening-hours"><h3>' . esc_html__('Opening Hours', 'artpulse') . '</h3><ul>';
                    foreach ($hours as $day => $vals) {
                        echo '<li><strong>' . esc_html(ucfirst($day) . ':') . '</strong> ' .
                            ($vals[2] ? esc_html__('Closed', 'artpulse') : esc_html(trim($vals[0] . ' - ' . $vals[1]))) .
                            '</li>';
                    }
                    echo '</ul></div>';
                }
                ?>
                <?php echo do_shortcode('[ap_org_profile_edit]'); ?>
            </details>
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
            echo '<li>' . esc_html($event->post_title);
            echo ' <a href="#" class="ap-inline-edit" data-id="' . $event->ID . '">Edit</a>';
            echo ' <button class="ap-delete-event" data-id="' . $event->ID . '">Delete</button></li>';
        }
        $html = ob_get_clean();

        wp_send_json_success(['updated_list_html' => $html]);
    }

    public static function handle_ajax_get_event() {
        check_ajax_referer('ap_org_dashboard_nonce', 'nonce');

        $event_id = intval($_POST['event_id'] ?? 0);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            wp_send_json_error(['message' => 'Invalid event.']);
        }

        if (!current_user_can('edit_post', $event_id)) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        $data = [
            'ap_event_title'          => get_the_title($event_id),
            'ap_event_date'           => get_post_meta($event_id, '_ap_event_date', true),
            'ap_event_start_date'     => get_post_meta($event_id, 'event_start_date', true),
            'ap_event_end_date'       => get_post_meta($event_id, 'event_end_date', true),
            'ap_event_location'       => get_post_meta($event_id, '_ap_event_location', true),
            'ap_venue_name'           => get_post_meta($event_id, 'venue_name', true),
            'ap_event_street_address' => get_post_meta($event_id, 'event_street_address', true),
            'ap_event_country'        => get_post_meta($event_id, 'event_country', true),
            'ap_event_state'          => get_post_meta($event_id, 'event_state', true),
            'ap_event_city'           => get_post_meta($event_id, 'event_city', true),
            'ap_event_postcode'       => get_post_meta($event_id, 'event_postcode', true),
            'address_components'      => get_post_meta($event_id, 'address_components', true),
            'ap_event_organizer_name' => get_post_meta($event_id, 'event_organizer_name', true),
            'ap_event_organizer_email'=> get_post_meta($event_id, 'event_organizer_email', true),
            'ap_event_type'           => current($terms = wp_get_post_terms($event_id, 'artpulse_event_type', ['fields' => 'ids'])) ?: '',
            'ap_event_featured'       => get_post_meta($event_id, 'event_featured', true),
            'ap_event_rsvp_enabled'   => get_post_meta($event_id, 'event_rsvp_enabled', true),
            'ap_event_rsvp_limit'     => get_post_meta($event_id, 'event_rsvp_limit', true),
            'ap_event_waitlist_enabled' => get_post_meta($event_id, 'event_waitlist_enabled', true),
        ];

        wp_send_json_success($data);
    }

    public static function handle_ajax_update_event() {
        check_ajax_referer('ap_org_dashboard_nonce', 'nonce');

        $event_id = intval($_POST['ap_event_id'] ?? 0);
        if (!$event_id || get_post_type($event_id) !== 'artpulse_event') {
            wp_send_json_error(['message' => 'Invalid event.']);
        }

        if (!current_user_can('edit_post', $event_id)) {
            wp_send_json_error(['message' => 'Permission denied.']);
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
        $rsvp_enabled     = isset($_POST['ap_event_rsvp_enabled']) ? '1' : '0';
        $rsvp_limit       = isset($_POST['ap_event_rsvp_limit']) ? intval($_POST['ap_event_rsvp_limit']) : 0;
        $waitlist_enabled = isset($_POST['ap_event_waitlist_enabled']) ? '1' : '0';

        wp_update_post([
            'ID'         => $event_id,
            'post_title' => $title,
        ]);

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
        update_post_meta($event_id, 'event_featured', $featured);
        update_post_meta($event_id, 'event_rsvp_enabled', $rsvp_enabled);
        update_post_meta($event_id, 'event_rsvp_limit', $rsvp_limit);
        update_post_meta($event_id, 'event_waitlist_enabled', $waitlist_enabled);

        if ($event_type) {
            wp_set_post_terms($event_id, [$event_type], 'artpulse_event_type');
        }

        // Reload the event list for this organization
        $org_id = intval(get_post_meta($event_id, '_ap_event_organization', true));
        ob_start();
        $events = get_posts([
            'post_type'   => 'artpulse_event',
            'post_status' => ['publish','pending','draft'],
            'meta_key'    => '_ap_event_organization',
            'meta_value'  => $org_id,
        ]);
        foreach ($events as $event) {
            echo '<li>' . esc_html($event->post_title)
                 . ' <a href="#" class="ap-inline-edit" data-id="' . $event->ID . '">Edit</a>'
                 . ' <button class="ap-delete-event" data-id="' . $event->ID . '">Delete</button></li>';
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
            echo '<li>' . esc_html($event->post_title);
            echo ' <a href="#" class="ap-inline-edit" data-id="' . $event->ID . '">Edit</a>';
            echo ' <button class="ap-delete-event" data-id="' . $event->ID . '">Delete</button></li>';
        }
        $html = ob_get_clean();

        wp_send_json_success(['updated_list_html' => $html]);
    }
}
