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

        wp_enqueue_script('ap-org-submission-js');

        $fields = \ArtPulse\Admin\MetaBoxesOrganisation::get_registered_org_meta_fields();
        // Avoid duplicate name input; the title field already captures the
        // organization name.
        unset($fields['ead_org_name']);

        ob_start();
        ?>
        <form class="ap-org-submission-form ap-form-container" enctype="multipart/form-data">
            <p>
                <label class="ap-form-label" for="ap-org-title"><?php esc_html_e('Organization Name', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-org-title" type="text" name="title" required />
            </p>
            <p>
                <label class="ap-form-label" for="ap-org-country"><?php esc_html_e('Country*', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-org-country" type="text" class="ap-address-country ap-address-input" data-required="<?php esc_attr_e('Country is required', 'artpulse'); ?>" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-org-state"><?php esc_html_e('State/Province', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-org-state" type="text" class="ap-address-state ap-address-input" />
            </p>
            <p>
                <label class="ap-form-label" for="ap-org-city"><?php esc_html_e('City', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-org-city" type="text" class="ap-address-city ap-address-input" />
            </p>
            <?php foreach ($fields as $key => $args) {
                list($type, $label) = $args;
                ?>
                <p>
                    <label class="ap-form-label" for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><br>
                    <?php
                    switch ($type) {
                        case 'textarea':
                            printf('<textarea class="ap-form-textarea" id="%1$s" name="%1$s"%2$s></textarea>', esc_attr($key), $key === 'ead_org_description' ? ' required' : '');
                            break;
                        case 'checkbox':
                            printf('<input class="ap-form-input" id="%1$s" type="checkbox" name="%1$s" value="1" />', esc_attr($key));
                            break;
                        case 'select':
                            if ($key === 'ead_org_type') {
                                $opts = ['gallery', 'museum', 'studio', 'collective', 'non-profit', 'commercial-gallery', 'public-art-space', 'educational-institution', 'other'];
                                echo '<select class="ap-form-select" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '">';
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
                                    '<input class="ap-form-input" id="%1$s" type="text" name="%1$s"%2$s />',
                                    esc_attr($key),
                                    $extra_class
                                );
                            }
                            break;
                        case 'media':
                            printf('<input class="ap-form-input" id="%1$s" type="file" name="%1$s" accept="image/*" />', esc_attr($key));
                            break;
                        default:
                            $req = $key === 'ead_org_primary_contact_email' ? ' required' : '';
                            printf('<input class="ap-form-input" id="%1$s" type="%2$s" name="%1$s"%3$s />', esc_attr($key), esc_attr($type), $req);
                    }
                    ?>
                </p>
            <?php } ?>
            <p>
                <label class="ap-form-label" for="ap-org-images"><?php esc_html_e('Images (max 5)', 'artpulse'); ?></label><br>
                <input class="ap-form-input" id="ap-org-images" type="file" name="images[]" accept="image/*" multiple />
                <input class="ap-form-input" type="hidden" name="address_components" id="ap-org-address-components" />
            </p>
            <p>
                <button class="ap-form-button" type="submit"><?php esc_html_e('Submit', 'artpulse'); ?></button>
            </p>
        </form>
        <div class="ap-form-messages" role="status" aria-live="polite"></div>
        <?php
        return ob_get_clean();
    }
}
