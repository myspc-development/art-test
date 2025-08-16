<?php

namespace ArtPulse\Frontend;

class EventService {
    /**
     * Validate common event data and create the post with meta.
     *
     * @param array $data    Sanitized event data.
     * @param int   $user_id Current user ID.
     * @return int|\WP_Error Event ID on success or WP_Error on failure.
     */
    public static function create_event(array $data, int $user_id) {
        $title      = trim($data['title'] ?? '');
        $description = $data['description'] ?? '';
        $date       = trim($data['date'] ?? '');
        $start_date = trim($data['start_date'] ?? '');
        $end_date   = trim($data['end_date'] ?? '');
        $recurrence = trim($data['recurrence'] ?? '');
        $location   = trim($data['location'] ?? '');
        $venue      = trim($data['venue_name'] ?? '');
        $street     = trim($data['street'] ?? '');
        $country    = trim($data['country'] ?? '');
        $state      = trim($data['state'] ?? '');
        $city       = trim($data['city'] ?? '');
        $postcode   = trim($data['postcode'] ?? '');
        $address_components = $data['address_components'] ?? null;
        $address_full = trim($data['address_full'] ?? '');
        $start_time  = trim($data['start_time'] ?? '');
        $end_time    = trim($data['end_time'] ?? '');
        $contact     = trim($data['contact_info'] ?? '');
        $rsvp        = trim($data['rsvp_url'] ?? '');
        $org_name    = trim($data['organizer_name'] ?? '');
        $org_email   = trim($data['organizer_email'] ?? '');
        $event_type  = intval($data['event_type'] ?? 0);
        $featured    = $data['featured'] ?? '0';
        $org_id      = intval($data['org_id'] ?? 0);
        $artists     = $data['artists'] ?? [];
        $post_status = $data['post_status'] ?? 'pending';
        $post_date   = $data['post_date'] ?? null;

        if (empty($title)) {
            return new \WP_Error('missing_title', __('Please enter an event title.', 'artpulse'));
        }
        if (empty($date)) {
            return new \WP_Error('missing_date', __('Please enter an event date.', 'artpulse'));
        }
        if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date)) {
            return new \WP_Error('invalid_date', __('Please enter a valid date in YYYY-MM-DD format.', 'artpulse'));
        }
        if ($start_date && $end_date && strtotime($start_date) > strtotime($end_date)) {
            return new \WP_Error('invalid_range', __('Start date cannot be later than end date.', 'artpulse'));
        }
        if ($org_id <= 0) {
            return new \WP_Error('missing_org', __('Please select an organization.', 'artpulse'));
        }

        $authorized = [];
        $user_orgs  = get_posts([
            'post_type'   => 'artpulse_org',
            'author'      => $user_id,
            'numberposts' => -1,
        ]);
        foreach ($user_orgs as $post) {
            $authorized[] = intval(is_object($post) ? $post->ID : ($post['ID'] ?? 0));
        }
        $meta_org = intval(get_user_meta($user_id, 'ap_organization_id', true));
        if ($meta_org) {
            $authorized[] = $meta_org;
        }
        if (!in_array($org_id, $authorized, true)) {
            return new \WP_Error('invalid_org', __('Invalid organization selected.', 'artpulse'));
        }

        $post_args = [
            'post_type'   => 'artpulse_event',
            'post_status' => $post_status,
            'post_title'  => $title,
            'post_content'=> $description,
            'post_author' => $user_id,
        ];
        if ($post_date) {
            $post_args['post_date'] = $post_date;
        }

        $post_id = wp_insert_post($post_args);
        if (is_wp_error($post_id) || !$post_id) {
            return new \WP_Error('insert_failed', __('Error submitting event. Please try again later.', 'artpulse'));
        }

        update_post_meta($post_id, '_ap_event_date', $date);
        update_post_meta($post_id, 'event_start_date', $start_date);
        update_post_meta($post_id, 'event_end_date', $end_date);
        if ($recurrence !== '') {
            update_post_meta($post_id, 'event_recurrence_rule', $recurrence);
        }
        update_post_meta($post_id, '_ap_event_location', $location);
        update_post_meta($post_id, 'venue_name', $venue);
        update_post_meta($post_id, 'event_street_address', $street);
        update_post_meta($post_id, 'event_country', $country);
        update_post_meta($post_id, 'event_state', $state);
        update_post_meta($post_id, 'event_city', $city);
        update_post_meta($post_id, 'event_postcode', $postcode);
        if (is_array($address_components)) {
            $encoded = function_exists('wp_json_encode') ? wp_json_encode($address_components) : json_encode($address_components);
            update_post_meta($post_id, 'address_components', $encoded);
        } elseif ($address_components) {
            update_post_meta($post_id, 'address_components', $address_components);
        }
        update_post_meta($post_id, '_ap_event_address', $address_full);
        update_post_meta($post_id, '_ap_event_start_time', $start_time);
        update_post_meta($post_id, '_ap_event_end_time', $end_time);
        update_post_meta($post_id, '_ap_event_contact', $contact);
        update_post_meta($post_id, '_ap_event_rsvp', $rsvp);
        update_post_meta($post_id, 'event_organizer_name', $org_name);
        update_post_meta($post_id, 'event_organizer_email', $org_email);
        update_post_meta($post_id, '_ap_event_organization', $org_id);
        if (!empty($artists)) {
            update_post_meta($post_id, '_ap_event_artists', $artists);
        }
        update_post_meta($post_id, 'event_featured', $featured);

        if ($event_type) {
            wp_set_post_terms($post_id, [$event_type], 'event_type');
        }

        return $post_id;
    }
}
