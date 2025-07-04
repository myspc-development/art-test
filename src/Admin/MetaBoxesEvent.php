<?php
namespace ArtPulse\Admin;

class MetaBoxesEvent {

    public static function register() {
        // Use the post type specific hook so the meta box is always added even
        // if this class registers after the generic `add_meta_boxes` action runs.
        add_action('add_meta_boxes_artpulse_event', [self::class, 'add_event_meta_boxes']);
        add_action('save_post_artpulse_event', [self::class, 'save_event_meta'], 10, 2); // Corrected CPT slug
        add_action('rest_api_init', [self::class, 'register_rest_fields']);
        add_action('restrict_manage_posts', [self::class, 'add_admin_filters']);
        add_filter('pre_get_posts', [self::class, 'filter_admin_query']);
    }

    public static function add_event_meta_boxes() {
        add_meta_box(
            'ead_event_details',
            __('Event Details', 'artpulse'),
            [self::class, 'render_event_details'],
            'artpulse_event', // Corrected CPT slug
            'normal',
            'high'
        );
    }

    public static function render_event_details($post) {
        wp_nonce_field('ead_event_meta_nonce', 'ead_event_meta_nonce_field');

        $fields  = self::get_registered_event_meta_fields();
        $opts    = get_option('artpulse_settings', []);
        $def_lim = isset($opts['default_rsvp_limit']) ? absint($opts['default_rsvp_limit']) : 50;
        $min_lim = absint($opts['min_rsvp_limit'] ?? 0);
        $max_lim = absint($opts['max_rsvp_limit'] ?? 0);
        $def_wait= !empty($opts['waitlists_enabled']);

        echo '<table class="form-table">';
        foreach ($fields as $key => $args) {
            $type  = $args['type'];
            $label = $args['label'];
            $value = get_post_meta($post->ID, $key, true);
            if (!metadata_exists('post', $post->ID, $key)) {
                if ($key === 'event_rsvp_limit') {
                    $value = $def_lim;
                } elseif ($key === 'event_waitlist_enabled') {
                    $value = $def_wait ? '1' : '0';
                }
            }
            echo '<tr><th><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>';
            switch ($type) {
                case 'date':
                case 'email':
                case 'text':
                case 'url':
                    echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" />';
                    break;
                case 'number':
                    $extra = '';
                    if ($key === 'event_rsvp_limit') {
                        if ($min_lim) { $extra .= ' min="' . esc_attr($min_lim) . '"'; }
                        if ($max_lim) { $extra .= ' max="' . esc_attr($max_lim) . '"'; }
                    }
                    echo '<input type="number" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text"' . $extra . ' />';
                    break;
                case 'checkbox': // 'boolean' is more consistent with other files, but 'checkbox' works
                    if ($key === 'event_waitlist_enabled' && !$def_wait) {
                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="0" />' . esc_html__('Waitlists disabled', 'artpulse');
                    } else {
                        echo '<input type="checkbox" name="' . esc_attr($key) . '" value="1" ' . checked($value, '1', false) . ' />';
                    }
                    break;
                case 'textarea':
                    echo '<textarea name="' . esc_attr($key) . '" rows="4" class="large-text">' . esc_textarea($value) . '</textarea>';
                    break;
                case 'media': // This is usually a number (attachment ID)
                    echo '<input type="number" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . __('Media Library ID', 'artpulse') . '" />';
                    // Consider adding a media uploader button here for better UX
                    break;
                case 'post_select':
                    $post_type = $args['post_type'] ?? 'post';
                    $posts = get_posts([
                        'post_type'   => $post_type,
                        'numberposts' => -1,
                        'post_status' => 'publish',
                    ]);
                    echo '<select name="' . esc_attr($key) . '" class="regular-text">';
                    echo '<option value="">' . esc_html__('Select', 'artpulse') . '</option>';
                    foreach ($posts as $p) {
                        echo '<option value="' . esc_attr($p->ID) . '"' . selected((int)$value, $p->ID, false) . '>' . esc_html($p->post_title) . '</option>';
                    }
                    echo '</select>';
                    break;
                default:
                    echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" />';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    public static function save_event_meta($post_id, $post) {
        if (!isset($_POST['ead_event_meta_nonce_field']) || !wp_verify_nonce($_POST['ead_event_meta_nonce_field'], 'ead_event_meta_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type !== 'artpulse_event') return; // Corrected CPT slug

        $fields = self::get_registered_event_meta_fields();
        foreach ($fields as $field => $args) {
            $type  = $args['type'];
            $value = $_POST[$field] ?? '';

            if ($type === 'checkbox') {
                $value = isset($_POST[$field]) ? '1' : '0';
            } elseif ($type === 'email' && !empty($value) && !is_email($value)) {
                continue; // Skip if email is invalid
            } elseif ($type === 'media' && !empty($value) && !is_numeric($value)) {
                continue; // Skip if media ID is not numeric
            } elseif ($type === 'post_select') {
                $value = intval($value);
                if ($value <= 0) {
                    delete_post_meta($post_id, $field);
                    continue;
                }
            } elseif ($type === 'number') {
                $value = is_numeric($value) ? intval($value) : 0;
            } elseif ($type === 'textarea') {
                $value = sanitize_textarea_field($value);
            } elseif ($type === 'url' && !empty($value)) {
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    continue; // Skip if URL is invalid
                }
                $value = esc_url_raw($value);
            }

            if ($type === 'textarea') {
                if (in_array($field, ['event_rsvp_list', 'event_waitlist'])) {
                    $arr = array_filter(array_map('trim', explode(',', $value)));
                    update_post_meta($post_id, $field, $arr);
                } else {
                    update_post_meta($post_id, $field, $value);
                }
            } elseif ($type === 'number') {
                update_post_meta($post_id, $field, $value);
            } elseif ($type === 'url') {
                update_post_meta($post_id, $field, esc_url_raw($value));
            } else {
                update_post_meta($post_id, $field, sanitize_text_field($value));
            }
        }
    }

    private static function get_registered_event_meta_fields() {
        // Note: Additional address fields are managed by the MetaBoxesAddress helper.
        // The basic `_ap_event_address` field is provided here for convenience.
        return [
            '_ap_event_date'        => ['type' => 'date', 'label' => __('Event Date', 'artpulse')],
            '_ap_event_location'    => ['type' => 'text', 'label' => __('Location', 'artpulse')],
            '_ap_event_address'     => ['type' => 'text', 'label' => __('Address', 'artpulse')],
            '_ap_event_start_time'  => ['type' => 'text', 'label' => __('Start Time', 'artpulse')],
            '_ap_event_end_time'    => ['type' => 'text', 'label' => __('End Time', 'artpulse')],
            '_ap_event_contact'     => ['type' => 'text', 'label' => __('Contact', 'artpulse')],
            '_ap_event_rsvp'        => ['type' => 'url',  'label' => __('RSVP URL', 'artpulse')],
            'event_start_date'      => ['type' => 'date', 'label' => __('Start Date', 'artpulse')],
            'event_end_date'        => ['type' => 'date', 'label' => __('End Date', 'artpulse')],
            'event_recurrence_rule' => ['type' => 'text', 'label' => __('Recurrence Rule', 'artpulse')],
            'venue_name'            => ['type' => 'text', 'label' => __('Venue Name', 'artpulse')],
            // Address fields (street_address, city, state, country, postcode) would
            // normally come from MetaBoxesAddress if it has been registered
            '_ap_event_organization' => [
                'type'      => 'post_select',
                'label'     => __('Main Organization', 'artpulse'),
                'post_type' => 'artpulse_org'
            ],
            'event_organizer_name'  => ['type' => 'text', 'label' => __('Organizer Name', 'artpulse')],
            'event_organizer_email' => ['type' => 'email', 'label' => __('Organizer Email', 'artpulse')],
            'event_banner_id'       => ['type' => 'media', 'label' => __('Event Banner (Media Library ID)', 'artpulse')],
            'event_featured'        => ['type' => 'checkbox', 'label' => __('Request Featured', 'artpulse')],
            'event_rsvp_enabled'    => ['type' => 'checkbox', 'label' => __('Enable RSVPs', 'artpulse')],
            'event_rsvp_limit'      => ['type' => 'number',   'label' => __('RSVP Limit', 'artpulse')],
            'event_waitlist_enabled'=> ['type' => 'checkbox', 'label' => __('Enable Waitlist', 'artpulse')],
            '_ap_event_artists'     => ['type' => 'text', 'label' => __('Co‑Host Artists', 'artpulse')],
            'event_rsvp_list'       => ['type' => 'textarea', 'label' => __('RSVP List', 'artpulse')],
            'event_waitlist'        => ['type' => 'textarea', 'label' => __('Waitlist', 'artpulse')],
            'event_attended'        => ['type' => 'textarea', 'label' => __('Attended', 'artpulse')],
        ];
    }

    public static function register_rest_fields() {
        foreach (self::get_registered_event_meta_fields() as $field => $args) {
            register_rest_field('artpulse_event', $field, [ // Corrected CPT slug
                'get_callback'    => fn($object) => get_post_meta($object['id'], $field, true),
                'update_callback' => fn($value, $object) => update_post_meta(
                    $object->ID,
                    $field,
                    $args['type'] === 'url' ? esc_url_raw($value) : sanitize_text_field($value)
                ),
                'schema'          => [
                    'type' => match ($args['type']) {
                        'checkbox'                => 'boolean',
                        'media', 'post_select', 'number' => 'integer',
                        default                  => 'string'
                    }
                ],
            ]);
        }
    }

    public static function add_admin_filters() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'artpulse_event') return; // Corrected CPT slug
        $selected = $_GET['event_featured'] ?? '';
        echo '<select name="event_featured">
            <option value="">' . __('Filter by Featured', 'artpulse') . '</option>
            <option value="1"' . selected($selected, '1', false) . '>Yes</option>
            <option value="0"' . selected($selected, '0', false) . '>No</option>
        </select>';
    }

    public static function filter_admin_query($query) {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'artpulse_event') return; // Corrected CPT slug

        if (isset($_GET['event_featured']) && $_GET['event_featured'] !== '') {
            $query->set('meta_key', 'event_featured');
            $query->set('meta_value', $_GET['event_featured']);
        }
    }
}