<?php
namespace ArtPulse\Frontend;

use ArtPulse\Admin\MetaBoxesOrganisation;

class OrgProfileEditShortcode {
    public static function register() {
        add_shortcode('ap_org_profile_edit', [self::class, 'render_form']);
        add_action('init', [self::class, 'handle_form_submission']);
    }

    public static function render_form() {
        if (!is_user_logged_in()) {
            return '<p>You must be logged in to edit your organization.</p>';
        }

        $user_id = get_current_user_id();
        $org_id  = get_user_meta($user_id, 'ap_organization_id', true);
        if (!$org_id) {
            return '<p>No organization assigned.</p>';
        }

        $org_post = get_post($org_id);
        $fields   = MetaBoxesOrganisation::get_registered_org_meta_fields();
        unset($fields['ead_org_name']);

        $address_json = get_post_meta($org_id, 'address_components', true);
        $components   = $address_json ? json_decode($address_json, true) : [];
        $country      = $components['country'] ?? '';
        $state        = $components['state'] ?? '';
        $city         = $components['city'] ?? '';

        $output = '';
        if (isset($_GET['ap_updated'])) {
            $output .= '<div class="notice success">Organization updated successfully.</div>';
        }

        ob_start();
        ?>
        <form method="post" enctype="multipart/form-data" class="ap-org-profile-edit-form">
            <?php wp_nonce_field('ap_org_profile_edit_action', 'ap_org_profile_nonce'); ?>
            <p>
                <label for="post_title"><?php esc_html_e('Organization Name', 'artpulse-management'); ?></label><br>
                <input type="text" id="post_title" name="post_title" value="<?php echo esc_attr($org_post->post_title); ?>" required>
            </p>
            <p>
                <label for="ap_org_country"><?php esc_html_e('Country', 'artpulse-management'); ?></label><br>
                <select id="ap_org_country" class="ap-address-country ap-address-input" name="ap_org_country" data-selected="<?php echo esc_attr($country); ?>"></select>
            </p>
            <p>
                <label for="ap_org_state"><?php esc_html_e('State/Province', 'artpulse-management'); ?></label><br>
                <select id="ap_org_state" class="ap-address-state ap-address-input" name="ap_org_state" data-selected="<?php echo esc_attr($state); ?>"></select>
            </p>
            <p>
                <label for="ap_org_city"><?php esc_html_e('City', 'artpulse-management'); ?></label><br>
                <select id="ap_org_city" class="ap-address-city ap-address-input" name="ap_org_city" data-selected="<?php echo esc_attr($city); ?>"></select>
            </p>
            <input type="hidden" name="address_components" id="ap-org-address-components" value="<?php echo esc_attr(json_encode(['country' => $country, 'state' => $state, 'city' => $city])); ?>">
            <?php foreach ($fields as $key => $args) {
                list($type, $label) = $args;
                $value = get_post_meta($org_id, $key, true);
                ?>
                <p>
                    <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><br>
                    <?php
                    switch ($type) {
                        case 'textarea':
                            echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">' . esc_textarea($value) . '</textarea>';
                            break;
                        case 'checkbox':
                            echo '<input id="' . esc_attr($key) . '" type="checkbox" name="' . esc_attr($key) . '" value="1" ' . checked($value, '1', false) . ' />';
                            break;
                        case 'select':
                            if ($key === 'ead_org_type') {
                                $opts = ['gallery','museum','studio','collective','non-profit','commercial-gallery','public-art-space','educational-institution','other'];
                                echo '<select id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
                                echo '<option value="">' . esc_html__('Select', 'artpulse-management') . '</option>';
                                foreach ($opts as $opt) {
                                    echo '<option value="' . esc_attr($opt) . '" ' . selected($value, $opt, false) . '>' . esc_html(ucfirst(str_replace('-', ' ', $opt))) . '</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<input id="' . esc_attr($key) . '" type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
                            }
                            break;
                        case 'media':
                            $img = $value ? wp_get_attachment_url($value) : '';
                            if ($img) {
                                echo '<img src="' . esc_url($img) . '" alt="" style="max-width:100px;" /><br>';
                            }
                            echo '<input id="' . esc_attr($key) . '" type="file" name="' . esc_attr($key) . '" accept="image/*" />';
                            break;
                        case 'email':
                            echo '<input id="' . esc_attr($key) . '" type="email" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
                            break;
                        case 'url':
                            echo '<input id="' . esc_attr($key) . '" type="url" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
                            break;
                        default:
                            echo '<input id="' . esc_attr($key) . '" type="' . esc_attr($type) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
                    }
                    ?>
                </p>
            <?php } ?>
            <p>
                <input type="submit" name="ap_org_profile_submit" value="<?php esc_attr_e('Update Organization', 'artpulse-management'); ?>">
            </p>
        </form>
        <?php
        return $output . ob_get_clean();
    }

    public static function handle_form_submission() {
        if (!isset($_POST['ap_org_profile_submit']) || !is_user_logged_in()) {
            return;
        }

        if (!isset($_POST['ap_org_profile_nonce']) || !wp_verify_nonce($_POST['ap_org_profile_nonce'], 'ap_org_profile_edit_action')) {
            return;
        }

        $user_id = get_current_user_id();
        $org_id  = get_user_meta($user_id, 'ap_organization_id', true);
        if (!$org_id) {
            return;
        }

        $title = sanitize_text_field($_POST['post_title'] ?? '');
        wp_update_post([
            'ID'         => $org_id,
            'post_title' => $title,
        ]);

        $fields = MetaBoxesOrganisation::get_registered_org_meta_fields();
        unset($fields['ead_org_name']);

        if (!empty($_POST['address_components'])) {
            $decoded = json_decode(stripslashes($_POST['address_components']), true);
            if (is_array($decoded)) {
                update_post_meta($org_id, 'address_components', wp_json_encode([
                    'country' => sanitize_text_field($decoded['country'] ?? ''),
                    'state'   => sanitize_text_field($decoded['state'] ?? ''),
                    'city'    => sanitize_text_field($decoded['city'] ?? ''),
                ]));
            }
        }

        foreach ($fields as $key => $args) {
            $type = $args[0];
            if ($type === 'media') {
                if (!empty($_FILES[$key]['tmp_name'])) {
                    $uploaded = media_handle_upload($key, 0);
                    if (!is_wp_error($uploaded)) {
                        update_post_meta($org_id, $key, $uploaded);
                    }
                } elseif (isset($_POST[$key]) && is_numeric($_POST[$key])) {
                    update_post_meta($org_id, $key, intval($_POST[$key]));
                }
                continue;
            }

            $value = $_POST[$key] ?? '';
            if ($type === 'textarea') {
                $san = sanitize_textarea_field($value);
            } elseif ($type === 'url') {
                $san = esc_url_raw($value);
            } elseif ($type === 'email') {
                $san = sanitize_email($value);
            } elseif ($type === 'checkbox') {
                $san = isset($_POST[$key]) ? '1' : '0';
            } else {
                $san = sanitize_text_field($value);
            }
            update_post_meta($org_id, $key, $san);
        }

        wp_redirect(add_query_arg('ap_updated', '1', wp_get_referer()));
        exit;
    }
}
