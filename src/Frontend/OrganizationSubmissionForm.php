<?php
namespace ArtPulse\Frontend;

class OrganizationSubmissionForm {
    public static function register(): void {
        add_shortcode('ap_submit_organization', [self::class, 'render']);
    }

    public static function render(): string {
        if (!defined('ARTPULSE_PLUGIN_FILE')) {
            return '';
        }

        if (function_exists('ap_enqueue_global_styles')) {
            ap_enqueue_global_styles();
        }

        wp_enqueue_script('ap-org-submission-js');

        $fields = \ArtPulse\Admin\MetaBoxesOrganisation::get_registered_org_meta_fields();
        // Avoid duplicate name input; the title field already captures the
        // organization name.
        unset($fields['ead_org_name']);

        ob_start();
        ?>
        <form class="ap-org-submission-form ap-form-container" enctype="multipart/form-data">
            <div class="form-group">
                <label class="ap-form-label" for="ap-org-title"><?php esc_html_e('Organization Name', 'artpulse'); ?></label>
                <input class="ap-input" id="ap-org-title" type="text" name="title" required />
            </div>
            <div class="form-group">
                <label class="ap-form-label" for="ap-org-country"><?php esc_html_e('Country*', 'artpulse'); ?></label>
                <input class="ap-input ap-address-country ap-address-input" id="ap-org-country" type="text" data-required="<?php esc_attr_e('Country is required', 'artpulse'); ?>" />
            </div>
            <div class="form-group">
                <label class="ap-form-label" for="ap-org-state"><?php esc_html_e('State/Province', 'artpulse'); ?></label>
                <input class="ap-input ap-address-state ap-address-input" id="ap-org-state" type="text" />
            </div>
            <div class="form-group">
                <label class="ap-form-label" for="ap-org-city"><?php esc_html_e('City', 'artpulse'); ?></label>
                <input class="ap-input ap-address-city ap-address-input" id="ap-org-city" type="text" />
            </div>
            <?php foreach ($fields as $key => $args) {
                list($type, $label) = $args;
                ?>
                <div class="form-group">
                    <label class="ap-form-label" for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
                    <?php
                    switch ($type) {
                        case 'textarea':
                            printf('<textarea class="ap-input" id="%1$s" name="%1$s"%2$s></textarea>', esc_attr($key), $key === 'ead_org_description' ? ' required' : '');
                            break;
                        case 'checkbox':
                            printf('<input class="ap-input" id="%1$s" type="checkbox" name="%1$s" value="1" />', esc_attr($key));
                            break;
                        case 'select':
                            if ($key === 'ead_org_type') {
                                $opts = ['gallery', 'museum', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other'];
                                echo '<select class="ap-input" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
                                echo '<option value="">' . esc_html__('Select', 'artpulse') . '</option>';
                                foreach ($opts as $opt) {
                                    echo '<option value="' . esc_attr($opt) . '">' . esc_html(ucfirst(str_replace('-', ' ', $opt))) . '</option>';
                                }
                                echo '</select>';
                            } else {
                                $extra_class = '';
                                if (in_array($key, ['ead_org_street_address', 'ead_org_venue_address'], true)) {
                                    $extra_class = ' class="ap-google-autocomplete"';
                                }
                                printf(
                                    '<input class="ap-input" id="%1$s" type="text" name="%1$s"%2$s />',
                                    esc_attr($key),
                                    $extra_class
                                );
                            }
                            break;
                        case 'media':
                            printf('<input class="ap-input" id="%1$s" type="file" name="%1$s" accept="image/*" />', esc_attr($key));
                            break;
                        default:
                            $req = $key === 'ead_org_primary_contact_email' ? ' required' : '';
                            printf('<input class="ap-input" id="%1$s" type="%2$s" name="%1$s"%3$s />', esc_attr($key), esc_attr($type), $req);
                    }
                    ?>
                </div>
            <?php } ?>
            <div class="form-group">
                <label class="ap-form-label" for="ap-org-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label>
                <input class="ap-input" id="ap-org-images" type="file" name="images[]" accept="image/*" multiple />
                <input class="ap-input" type="hidden" name="address_components" id="ap-org-address-components" />
            </div>
            <div class="form-group">
                <button class="ap-form-button nectar-button" type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
            </div>
        </form>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}
