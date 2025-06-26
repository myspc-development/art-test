<?php
namespace ArtPulse\Admin;

class MetaBoxesEvent {

    public static function register() {
        add_action('add_meta_boxes', [self::class, 'add_event_meta_boxes']);
        add_action('save_post_artpulse_event', [self::class, 'save_event_meta'], 10, 2); // Corrected CPT slug
        add_action('rest_api_init', [self::class, 'register_rest_fields']);
        add_action('restrict_manage_posts', [self::class, 'add_admin_filters']);
        add_filter('pre_get_posts', [self::class, 'filter_admin_query']);
    }

    public static function add_event_meta_boxes() {
        add_meta_box(
            'ead_event_details',
            __('Event Details', 'artpulse-management'),
            [self::class, 'render_event_details'],
            'artpulse_event', // Corrected CPT slug
            'normal',
            'high'
        );
    }

    public static function render_event_details($post) {
        wp_nonce_field('ead_event_meta_nonce', 'ead_event_meta_nonce_field');

        $fields = self::get_registered_event_meta_fields();

        echo '<table class="form-table">';
        foreach ($fields as $key => $args) {
            $type = $args['type'];
            $label = $args['label'];
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr><th><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>';
            switch ($type) {
                case 'date':
                case 'email':
                case 'text':
                    echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" />';
                    break;
                case 'checkbox': // 'boolean' is more consistent with other files, but 'checkbox' works
                    echo '<input type="checkbox" name="' . esc_attr($key) . '" value="1" ' . checked($value, '1', false) . ' />';
                    break;
                case 'media': // This is usually a number (attachment ID)
                    echo '<input type="number" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . __('Media Library ID', 'artpulse-management') . '" />';
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
                    echo '<option value="">' . esc_html__('Select', 'artpulse-management') . '</option>';
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
            $value = $_POST[$field] ?? '';
            if ($args['type'] === 'checkbox') {
                $value = isset($_POST[$field]) ? '1' : '0';
            } elseif ($args['type'] === 'email' && !empty($value) && !is_email($value)) {
                continue; // Skip if email is invalid
            } elseif ($args['type'] === 'media' && !empty($value) && !is_numeric($value)) {
                continue; // Skip if media ID is not numeric
            } elseif ($args['type'] === 'post_select') {
                $value = intval($value);
                if ($value <= 0) {
                    delete_post_meta($post_id, $field);
                    continue;
                }
            }
            // For date fields, you might want to add validation e.g., using strtotime or regex
            update_post_meta($post_id, $field, sanitize_text_field($value)); // sanitize_text_field is a good default
        }
    }

    private static function get_registered_event_meta_fields() {
        // Note: Address fields are managed by the MetaBoxesAddress helper and are
        // not registered here by default. If your event needs an address meta box
        // you can manually call MetaBoxesAddress::register(['artpulse_event']).
        return [
            'event_start_date'      => ['type' => 'date', 'label' => __('Start Date', 'artpulse-management')],
            'event_end_date'        => ['type' => 'date', 'label' => __('End Date', 'artpulse-management')],
            'venue_name'            => ['type' => 'text', 'label' => __('Venue Name', 'artpulse-management')],
            // Address fields (street_address, city, state, country, postcode) would
            // normally come from MetaBoxesAddress if it has been registered
            '_ap_event_organization' => [
                'type'      => 'post_select',
                'label'     => __('Main Organization', 'artpulse-management'),
                'post_type' => 'artpulse_org'
            ],
            'event_organizer_name'  => ['type' => 'text', 'label' => __('Organizer Name', 'artpulse-management')],
            'event_organizer_email' => ['type' => 'email', 'label' => __('Organizer Email', 'artpulse-management')],
            'event_banner_id'       => ['type' => 'media', 'label' => __('Event Banner (Media Library ID)', 'artpulse-management')],
            'event_featured'        => ['type' => 'checkbox', 'label' => __('Request Featured', 'artpulse-management')],
        ];
    }

    public static function register_rest_fields() {
        foreach (self::get_registered_event_meta_fields() as $field => $args) {
            register_rest_field('artpulse_event', $field, [ // Corrected CPT slug
                'get_callback'    => fn($object) => get_post_meta($object['id'], $field, true),
                'update_callback' => fn($value, $object) => update_post_meta($object->ID, $field, sanitize_text_field($value)), // Consider type-specific sanitization
                'schema'          => [
                    'type' => match ($args['type']) {
                        'checkbox'    => 'boolean',
                        'media',
                        'post_select' => 'integer',
                        default       => 'string'
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
            <option value="">' . __('Filter by Featured', 'artpulse-management') . '</option>
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